<?php
require_once __DIR__ . '/includes/fonctions.php';

$titrePage  = 'Inscription';
$pageActive = 'inscription';

// Données mémorisées par enregistrer.php en cas d'erreur (ou confirmation de réussite)
$reussite  = $_SESSION['inscription_reussie'] ?? null;
$anciennes = $_SESSION['anciennes_valeurs'] ?? [];
$erreurs   = $_SESSION['erreurs_champs'] ?? [];
unset($_SESSION['inscription_reussie'], $_SESSION['anciennes_valeurs'], $_SESSION['erreurs_champs']);

$val = function (string $cle) use ($anciennes): string {
    return e($anciennes[$cle] ?? '');
};
$err = function (string $cle) use ($erreurs): string {
    return e($erreurs[$cle] ?? '');
};
$classe = function (string $cle) use ($erreurs): string {
    return isset($erreurs[$cle]) ? ' invalide' : '';
};

require __DIR__ . '/includes/entete.php';
?>

<section class="page-formulaire">
    <div class="conteneur conteneur-etroit">

<?php if (is_array($reussite)): ?>

        <div class="panneau-succes">
            <div class="succes-icone" aria-hidden="true">✓</div>
            <h1>Demande envoyée !</h1>
            <p>
                Merci <strong><?= e($reussite['prenom'] ?? '') ?> <?= e($reussite['nom'] ?? '') ?></strong>,
                votre demande de carte étudiante a bien été enregistrée.
            </p>
            <p>L'administration va examiner votre dossier. Vous pourrez être contacté par téléphone ou par email à l'adresse
                <strong><?= e($reussite['email'] ?? '') ?></strong>.</p>
            <p class="actions-centre">
                <a class="btn btn-primaire" href="index.php">Retour à l'accueil</a>
                <a class="btn btn-secondaire" href="inscription.php">Inscrire un autre étudiant</a>
            </p>
        </div>

<?php else: ?>

        <div class="entete-page">
            <h1>Inscription à la carte étudiante</h1>
            <p>Les champs marqués d'un <span class="requis" aria-hidden="true">*</span><span class="sr-only">astérisque</span> sont obligatoires.</p>
        </div>

        <form id="form-inscription" class="formulaire" action="enregistrer.php" method="post" enctype="multipart/form-data" autocomplete="on">
            <?= champ_csrf() ?>

            <!-- Champ piège anti-robots : doit rester vide -->
            <div class="piege" aria-hidden="true">
                <label for="site_web">Ne pas remplir ce champ</label>
                <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
            </div>

            <div class="grille-2">
                <div class="champ<?= $classe('nom') ?>">
                    <label for="nom">Nom <span class="requis" aria-hidden="true">*</span></label>
                    <input type="text" id="nom" name="nom" required maxlength="60" autocomplete="family-name"
                           value="<?= $val('nom') ?>" aria-describedby="erreur-nom" placeholder="Ex. : Diop">
                    <p class="erreur-champ" id="erreur-nom" aria-live="polite"><?= $err('nom') ?></p>
                </div>

                <div class="champ<?= $classe('prenom') ?>">
                    <label for="prenom">Prénom <span class="requis" aria-hidden="true">*</span></label>
                    <input type="text" id="prenom" name="prenom" required maxlength="60" autocomplete="given-name"
                           value="<?= $val('prenom') ?>" aria-describedby="erreur-prenom" placeholder="Ex. : Aminata">
                    <p class="erreur-champ" id="erreur-prenom" aria-live="polite"><?= $err('prenom') ?></p>
                </div>
            </div>

            <div class="grille-2">
                <div class="champ<?= $classe('filiere') ?>">
                    <label for="filiere">Filière <span class="requis" aria-hidden="true">*</span></label>
                    <input type="text" id="filiere" name="filiere" required maxlength="100"
                           value="<?= $val('filiere') ?>" aria-describedby="erreur-filiere" placeholder="Ex. : Mathématiques, Informatique…">
                    <p class="erreur-champ" id="erreur-filiere" aria-live="polite"><?= $err('filiere') ?></p>
                </div>

                <div class="champ<?= $classe('niveau') ?>">
                    <label for="niveau">Niveau d'études <span class="requis" aria-hidden="true">*</span></label>
                    <select id="niveau" name="niveau" required aria-describedby="erreur-niveau">
                        <option value="" disabled<?= empty($anciennes['niveau']) ? ' selected' : '' ?>>Choisir votre niveau…</option>
                        <?php foreach (NIVEAUX as $niveau): ?>
                            <option value="<?= e($niveau) ?>"<?= (($anciennes['niveau'] ?? '') === $niveau) ? ' selected' : '' ?>><?= e($niveau) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="erreur-champ" id="erreur-niveau" aria-live="polite"><?= $err('niveau') ?></p>
                </div>
            </div>

            <div class="grille-2">
                <div class="champ<?= $classe('telephone') ?>">
                    <label for="telephone">Numéro de téléphone <span class="requis" aria-hidden="true">*</span></label>
                    <input type="tel" id="telephone" name="telephone" required maxlength="25" inputmode="tel" autocomplete="tel"
                           value="<?= $val('telephone') ?>" aria-describedby="aide-tel erreur-telephone" placeholder="Ex. : 77 123 45 67">
                    <p class="aide" id="aide-tel">Avec ou sans l'indicatif (+221).</p>
                    <p class="erreur-champ" id="erreur-telephone" aria-live="polite"><?= $err('telephone') ?></p>
                </div>

                <div class="champ<?= $classe('email') ?>">
                    <label for="email">Adresse email <span class="requis" aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" required maxlength="150" autocomplete="email"
                           value="<?= $val('email') ?>" aria-describedby="erreur-email" placeholder="Ex. : prenom.nom@exemple.com">
                    <p class="erreur-champ" id="erreur-email" aria-live="polite"><?= $err('email') ?></p>
                </div>
            </div>

            <div class="champ<?= $classe('photo') ?>">
                <label for="photo">Photo de l'étudiant <span class="optionnel">(facultatif)</span></label>
                <div class="zone-photo">
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp"
                           aria-describedby="aide-photo erreur-photo">
                    <div class="apercu-photo" id="apercu-photo" hidden>
                        <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="Aperçu de la photo choisie">
                    </div>
                </div>
                <p class="aide" id="aide-photo">Photo d'identité de face, au format JPG, PNG ou WebP — 2 Mo maximum.</p>
                <p class="erreur-champ" id="erreur-photo" aria-live="polite"><?= $err('photo') ?></p>
            </div>

            <div class="actions-form">
                <button type="submit" class="btn btn-primaire btn-grand btn-bloc" data-envoi>Envoyer ma demande</button>
                <a class="btn btn-secondaire btn-grand btn-bloc" href="index.php">Annuler</a>
            </div>

            <p class="mention">
                Les informations fournies servent uniquement à établir votre carte étudiante.
            </p>
        </form>

<?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/includes/pied.php'; ?>
