<?php
/**
 * Espace administrateur : liste, recherche, filtres, validation / rejet, export CSV.
 */
require_once __DIR__ . '/includes/fonctions.php';
exiger_admin();

$pdo = bdd();

/* ------------------------------------------------------------------
 *  Traitement des actions (POST) : valider / rejeter / remettre en attente
 * ------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On reconstruit l'URL de retour à partir de valeurs contrôlées
    $niveauRetour = nettoyer_texte($_POST['niveau'] ?? '');
    $statutRetour = nettoyer_texte($_POST['statut'] ?? '');
    $pageRetour   = max(1, (int) ($_POST['page'] ?? 1));
    $parametresRetour = array_filter([
        'q'      => nettoyer_texte($_POST['q'] ?? ''),
        'niveau' => in_array($niveauRetour, NIVEAUX, true) ? $niveauRetour : '',
        'statut' => in_array($statutRetour, ['en_attente', 'valide', 'rejete'], true) ? $statutRetour : '',
        'page'   => $pageRetour > 1 ? (string) $pageRetour : '',
    ], function ($v) { return $v !== ''; });
    $retour = 'admin.php' . ($parametresRetour ? '?' . http_build_query($parametresRetour) : '');

    if (!verifier_csrf($_POST['csrf'] ?? null)) {
        ajouter_message('erreur', 'Votre session de sécurité a expiré. Veuillez recommencer.');
        rediriger($retour);
    }

    $id     = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($id > 0 && in_array($action, ['valider', 'rejeter', 'remettre'], true)) {
        $stmt = $pdo->prepare('SELECT id, nom, prenom FROM etudiants WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $etudiant = $stmt->fetch();

        if (!$etudiant) {
            ajouter_message('erreur', 'Cette demande est introuvable.');
        } else {
            $identite = $etudiant['prenom'] . ' ' . $etudiant['nom'];
            try {
                if ($action === 'valider') {
                    // Le numéro et la date ne sont générés qu'une seule fois (COALESCE)
                    $maj = $pdo->prepare(
                        "UPDATE etudiants
                            SET statut = 'valide',
                                numero_carte = COALESCE(numero_carte, :numero),
                                date_delivrance = COALESCE(date_delivrance, :date)
                          WHERE id = :id"
                    );
                    $maj->execute([
                        ':numero' => numero_carte_pour((int) $etudiant['id']),
                        ':date'   => date('Y-m-d'),
                        ':id'     => $id,
                    ]);
                    ajouter_message('succes', 'La demande de ' . $identite . ' a été validée. Sa carte étudiante est prête.');
                } elseif ($action === 'rejeter') {
                    $maj = $pdo->prepare("UPDATE etudiants SET statut = 'rejete' WHERE id = :id");
                    $maj->execute([':id' => $id]);
                    ajouter_message('info', 'La demande de ' . $identite . ' a été rejetée.');
                } else {
                    $maj = $pdo->prepare("UPDATE etudiants SET statut = 'en_attente' WHERE id = :id");
                    $maj->execute([':id' => $id]);
                    ajouter_message('info', 'La demande de ' . $identite . ' a été remise en attente.');
                }
            } catch (PDOException $e) {
                error_log('Erreur mise à jour statut : ' . $e->getMessage());
                ajouter_message('erreur', "Une erreur est survenue lors de la mise à jour. Veuillez réessayer.");
            }
        }
    } else {
        ajouter_message('erreur', 'Action non valide.');
    }
    rediriger($retour);
}

/* ------------------------------------------------------------------
 *  Statistiques, filtres, pagination
 * ------------------------------------------------------------------ */
$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            SUM(statut = 'en_attente') AS attente,
            SUM(statut = 'valide') AS valides,
            SUM(statut = 'rejete') AS rejetes
       FROM etudiants"
)->fetch();

$q      = nettoyer_texte($_GET['q'] ?? '');
$niveauF = nettoyer_texte($_GET['niveau'] ?? '');
$niveauF = in_array($niveauF, NIVEAUX, true) ? $niveauF : '';
$statutF = nettoyer_texte($_GET['statut'] ?? '');
$statutF = in_array($statutF, ['en_attente', 'valide', 'rejete'], true) ? $statutF : '';

[$where, $params] = construire_filtres($_GET);

$parPage = 15;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiants $where");
$stmt->execute($params);
$totalResultats = (int) $stmt->fetchColumn();

$nbPages = max(1, (int) ceil($totalResultats / $parPage));
$page    = min(max(1, (int) ($_GET['page'] ?? 1)), $nbPages);
$decalage = ($page - 1) * $parPage;

$stmt = $pdo->prepare(
    "SELECT * FROM etudiants $where
     ORDER BY (statut = 'en_attente') DESC, date_inscription DESC, id DESC
     LIMIT $parPage OFFSET $decalage"
);
$stmt->execute($params);
$etudiants = $stmt->fetchAll();

$baseFiltres = array_filter(['q' => $q, 'niveau' => $niveauF, 'statut' => $statutF], function ($v) { return $v !== ''; });
$urlPage = function (int $p) use ($baseFiltres): string {
    $parametres = $baseFiltres;
    if ($p > 1) {
        $parametres['page'] = $p;
    }
    return 'admin.php' . ($parametres ? '?' . http_build_query($parametres) : '');
};
$urlExport = 'export_csv.php' . ($baseFiltres ? '?' . http_build_query($baseFiltres) : '');

/** Petit formulaire d'action (valider / rejeter / remettre) pour une ligne */
$boutonAction = function (array $et, string $action, string $libelle, string $classe, ?string $confirmation = null) use ($q, $niveauF, $statutF, $page): string {
    $html  = '<form method="post" action="admin.php" class="form-inline"';
    if ($confirmation !== null) {
        $html .= ' data-confirm="' . e($confirmation) . '"';
    }
    $html .= '>' . champ_csrf();
    $html .= '<input type="hidden" name="id" value="' . (int) $et['id'] . '">';
    $html .= '<input type="hidden" name="action" value="' . e($action) . '">';
    $html .= '<input type="hidden" name="q" value="' . e($q) . '">';
    $html .= '<input type="hidden" name="niveau" value="' . e($niveauF) . '">';
    $html .= '<input type="hidden" name="statut" value="' . e($statutF) . '">';
    $html .= '<input type="hidden" name="page" value="' . (int) $page . '">';
    $html .= '<button type="submit" class="btn btn-petit ' . e($classe) . '">' . e($libelle) . '</button>';
    return $html . '</form>';
};

$titrePage  = 'Administration';
$pageActive = 'admin';
require __DIR__ . '/includes/entete.php';
?>

<section class="section-admin">
    <div class="conteneur">

        <div class="admin-tete">
            <div>
                <h1>Demandes de carte étudiante</h1>
                <p class="sous-titre-section">Connecté en tant que <strong><?= e($_SESSION['admin_nom'] ?? '') ?></strong></p>
            </div>
            <a class="btn btn-secondaire" href="<?= e($urlExport) ?>">⬇ Exporter en CSV<?= $baseFiltres ? ' (résultats filtrés)' : '' ?></a>
        </div>

        <?php if (!empty($_SESSION['mdp_defaut'])): ?>
            <div class="alerte alerte-avertissement" role="alert">
                <p><strong>Sécurité :</strong> vous utilisez encore le mot de passe par défaut.
                    <a href="mot_de_passe.php">Changez-le maintenant</a>.</p>
            </div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat"><span class="stat-nombre"><?= (int) ($stats['total'] ?? 0) ?></span><span class="stat-libelle">Demandes</span></div>
            <div class="stat stat-attente"><span class="stat-nombre"><?= (int) ($stats['attente'] ?? 0) ?></span><span class="stat-libelle">En attente</span></div>
            <div class="stat stat-valide"><span class="stat-nombre"><?= (int) ($stats['valides'] ?? 0) ?></span><span class="stat-libelle">Validées</span></div>
            <div class="stat stat-rejete"><span class="stat-nombre"><?= (int) ($stats['rejetes'] ?? 0) ?></span><span class="stat-libelle">Rejetées</span></div>
        </div>

        <form class="filtres" method="get" action="admin.php" role="search">
            <div class="champ champ-recherche">
                <label for="q">Rechercher</label>
                <input type="search" id="q" name="q" value="<?= e($q) ?>" maxlength="100"
                       placeholder="Nom, prénom, filière ou niveau…">
            </div>
            <div class="champ">
                <label for="filtre-niveau">Niveau</label>
                <select id="filtre-niveau" name="niveau">
                    <option value="">Tous</option>
                    <?php foreach (NIVEAUX as $n): ?>
                        <option value="<?= e($n) ?>"<?= $niveauF === $n ? ' selected' : '' ?>><?= e($n) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="champ">
                <label for="filtre-statut">Statut</label>
                <select id="filtre-statut" name="statut">
                    <option value="">Tous</option>
                    <option value="en_attente"<?= $statutF === 'en_attente' ? ' selected' : '' ?>>En attente</option>
                    <option value="valide"<?= $statutF === 'valide' ? ' selected' : '' ?>>Validées</option>
                    <option value="rejete"<?= $statutF === 'rejete' ? ' selected' : '' ?>>Rejetées</option>
                </select>
            </div>
            <div class="filtres-boutons">
                <button type="submit" class="btn btn-primaire">Rechercher</button>
                <?php if ($baseFiltres): ?>
                    <a class="btn btn-secondaire" href="admin.php">Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>

        <p class="nb-resultats" role="status">
            <?= $totalResultats ?> résultat<?= $totalResultats > 1 ? 's' : '' ?>
            <?= $baseFiltres ? '(filtré)' : '' ?>
        </p>

        <?php if (!$etudiants): ?>
            <div class="vide">
                <p><strong>Aucune demande trouvée.</strong></p>
                <p><?= $baseFiltres ? 'Essayez de modifier ou de réinitialiser votre recherche.' : "Les inscriptions apparaîtront ici dès qu'un étudiant enverra le formulaire." ?></p>
            </div>
        <?php else: ?>
            <div class="tableau-wrap">
                <table class="table-resp">
                    <thead>
                        <tr>
                            <th scope="col">Étudiant</th>
                            <th scope="col">Filière</th>
                            <th scope="col">Niveau</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Inscrit le</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($etudiants as $et): ?>
                        <?php $urlImage = url_photo($et['photo']); ?>
                        <tr>
                            <td data-label="Étudiant">
                                <div class="cellule-etudiant">
                                    <?php if ($urlImage): ?>
                                        <img class="photo-mini" src="<?= e($urlImage) ?>" alt="Photo de <?= e($et['prenom'] . ' ' . $et['nom']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <span class="avatar" aria-hidden="true"><?= e(initiales($et['prenom'], $et['nom'])) ?></span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= e($et['nom']) ?> <?= e($et['prenom']) ?></strong>
                                        <?php if ($et['numero_carte']): ?><small>N° <?= e($et['numero_carte']) ?></small><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Filière"><?= e($et['filiere']) ?></td>
                            <td data-label="Niveau"><?= e($et['niveau']) ?></td>
                            <td data-label="Contact">
                                <a href="tel:<?= e($et['telephone']) ?>"><?= e($et['telephone']) ?></a><br>
                                <a class="email-cellule" href="mailto:<?= e($et['email']) ?>"><?= e($et['email']) ?></a>
                            </td>
                            <td data-label="Inscrit le"><?= e(date_fr($et['date_inscription'], true)) ?></td>
                            <td data-label="Statut"><?= badge_statut($et['statut']) ?></td>
                            <td data-label="Actions">
                                <div class="actions-ligne">
                                    <?php if ($et['statut'] === 'en_attente'): ?>
                                        <?= $boutonAction($et, 'valider', 'Valider', 'btn-primaire') ?>
                                        <?= $boutonAction($et, 'rejeter', 'Rejeter', 'btn-danger', 'Rejeter la demande de ' . $et['prenom'] . ' ' . $et['nom'] . ' ?') ?>
                                    <?php elseif ($et['statut'] === 'valide'): ?>
                                        <a class="btn btn-petit btn-secondaire" href="carte.php?id=<?= (int) $et['id'] ?>">Voir la carte</a>
                                        <?= $boutonAction($et, 'rejeter', 'Rejeter', 'btn-danger', 'Annuler la validation et rejeter la demande de ' . $et['prenom'] . ' ' . $et['nom'] . ' ?') ?>
                                    <?php else: ?>
                                        <?= $boutonAction($et, 'valider', 'Valider', 'btn-primaire') ?>
                                        <?= $boutonAction($et, 'remettre', 'Remettre en attente', 'btn-secondaire') ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($nbPages > 1): ?>
                <nav class="pagination" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= e($urlPage($page - 1)) ?>" rel="prev">← Précédent</a>
                    <?php endif; ?>
                    <span>Page <?= $page ?> sur <?= $nbPages ?></span>
                    <?php if ($page < $nbPages): ?>
                        <a href="<?= e($urlPage($page + 1)) ?>" rel="next">Suivant →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/includes/pied.php'; ?>
