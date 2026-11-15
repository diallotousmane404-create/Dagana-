<?php
/**
 * Connexion administrateur : mot de passe haché (bcrypt), CSRF, anti brute-force.
 */
require_once __DIR__ . '/includes/fonctions.php';

if (admin_connecte()) {
    rediriger('admin.php');
}

$erreur      = '';
$identifiant = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = nettoyer_texte($_POST['identifiant'] ?? '');
    $motDePasse  = is_string($_POST['mot_de_passe'] ?? null) ? $_POST['mot_de_passe'] : '';

    if (!verifier_csrf($_POST['csrf'] ?? null)) {
        $erreur = 'Votre session a expiré. Veuillez réessayer.';
    } elseif (connexion_bloquee()) {
        $erreur = 'Trop de tentatives échouées. Veuillez réessayer dans 15 minutes.';
    } elseif ($identifiant === '' || $motDePasse === '') {
        $erreur = 'Veuillez saisir votre identifiant et votre mot de passe.';
    } else {
        $stmt = bdd()->prepare('SELECT id, identifiant, mot_de_passe FROM administrateurs WHERE identifiant = :identifiant LIMIT 1');
        $stmt->execute([':identifiant' => $identifiant]);
        $admin = $stmt->fetch();

        // On vérifie toujours un hachage (même factice) pour garder un temps de réponse constant
        $hash   = $admin ? $admin['mot_de_passe'] : HASH_FACTICE;
        $valide = password_verify($motDePasse, $hash) && $admin;

        if ($valide) {
            effacer_echecs_connexion();

            // Met à jour le hachage si l'algorithme par défaut a évolué
            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                $maj = bdd()->prepare('UPDATE administrateurs SET mot_de_passe = :hash WHERE id = :id');
                $maj->execute([':hash' => password_hash($motDePasse, PASSWORD_DEFAULT), ':id' => $admin['id']]);
            }

            session_regenerate_id(true); // empêche la fixation de session
            unset($_SESSION['csrf']);
            $_SESSION['admin_id']          = (int) $admin['id'];
            $_SESSION['admin_nom']         = $admin['identifiant'];
            $_SESSION['derniere_activite'] = time();
            $_SESSION['mdp_defaut']        = hash_equals(MOT_DE_PASSE_PAR_DEFAUT, $motDePasse);

            ajouter_message('succes', 'Connexion réussie. Bienvenue, ' . $admin['identifiant'] . '.');
            rediriger('admin.php');
        }

        enregistrer_echec_connexion();
        usleep(400000); // ralentit les essais automatisés
        $erreur = 'Identifiant ou mot de passe incorrect.';
    }
}

$titrePage  = 'Connexion administrateur';
$pageActive = 'connexion';
require __DIR__ . '/includes/entete.php';
?>

<section class="page-formulaire">
    <div class="conteneur conteneur-mini">
        <div class="formulaire carte-connexion">
            <img class="connexion-logo" src="<?= e(LOGO) ?>" width="84" height="84" alt="">
            <h1>Espace administrateur</h1>
            <p class="centre">Accès réservé à l'équipe de l'<?= e(SIGLE) ?>.</p>

            <?php if ($erreur !== ''): ?>
                <div class="alerte alerte-erreur" role="alert"><p><?= e($erreur) ?></p></div>
            <?php endif; ?>

            <form action="connexion.php" method="post" autocomplete="off">
                <?= champ_csrf() ?>

                <div class="champ">
                    <label for="identifiant">Identifiant</label>
                    <input type="text" id="identifiant" name="identifiant" required maxlength="50"
                           value="<?= e($identifiant) ?>" autocomplete="username" autofocus>
                </div>

                <div class="champ">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required maxlength="200"
                           autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primaire btn-grand btn-bloc">Se connecter</button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/pied.php'; ?>
