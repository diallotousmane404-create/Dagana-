<?php
/**
 * Traitement du formulaire d'inscription :
 * CSRF -> validation -> doublons -> photo -> insertion (requête préparée PDO).
 */
require_once __DIR__ . '/includes/fonctions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rediriger('inscription.php');
}

if (!verifier_csrf($_POST['csrf'] ?? null)) {
    ajouter_message('erreur', 'Votre session a expiré. Veuillez remplir à nouveau le formulaire.');
    rediriger('inscription.php');
}

// Anti-robot : le champ « site_web » est invisible pour les humains
if (!empty($_POST['site_web'])) {
    rediriger('index.php');
}

$resultat = valider_inscription($_POST);
$donnees  = $resultat['donnees'];
$erreurs  = $resultat['erreurs'];

$pdo = bdd();

// ---- Doublons ----
if (!$erreurs) {
    $stmt = $pdo->prepare('SELECT id FROM etudiants WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $donnees['email']]);
    if ($stmt->fetch()) {
        $erreurs['email'] = 'Une demande a déjà été enregistrée avec cette adresse email.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id FROM etudiants WHERE telephone = :telephone AND nom = :nom AND prenom = :prenom LIMIT 1'
        );
        $stmt->execute([
            ':telephone' => $donnees['telephone'],
            ':nom'       => $donnees['nom'],
            ':prenom'    => $donnees['prenom'],
        ]);
        if ($stmt->fetch()) {
            $erreurs['telephone'] = 'Une demande existe déjà pour cet étudiant avec ce numéro de téléphone.';
        }
    }
}

// ---- Photo (facultative) ----
$photo = null;
if (!$erreurs) {
    [$photo, $erreurPhoto] = traiter_photo($_FILES['photo'] ?? null);
    if ($erreurPhoto !== null) {
        $erreurs['photo'] = $erreurPhoto;
    }
}

// ---- En cas d'erreur : on réaffiche le formulaire avec les valeurs saisies ----
if ($erreurs) {
    $photoEnvoyee = (($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK);
    $_SESSION['anciennes_valeurs'] = $donnees;
    $_SESSION['erreurs_champs']    = $erreurs;
    ajouter_message(
        'erreur',
        'Votre demande n\'a pas pu être envoyée. Veuillez corriger les champs signalés'
        . ($photoEnvoyee && !isset($erreurs['photo']) ? ' (pensez à sélectionner à nouveau votre photo).' : '.')
    );
    rediriger('inscription.php');
}

// ---- Enregistrement ----
try {
    $stmt = $pdo->prepare(
        'INSERT INTO etudiants (nom, prenom, filiere, niveau, telephone, email, photo)
         VALUES (:nom, :prenom, :filiere, :niveau, :telephone, :email, :photo)'
    );
    $stmt->execute([
        ':nom'       => $donnees['nom'],
        ':prenom'    => $donnees['prenom'],
        ':filiere'   => $donnees['filiere'],
        ':niveau'    => $donnees['niveau'],
        ':telephone' => $donnees['telephone'],
        ':email'     => $donnees['email'],
        ':photo'     => $photo,
    ]);
} catch (PDOException $e) {
    supprimer_photo($photo);
    $_SESSION['anciennes_valeurs'] = $donnees;

    if ($e->getCode() === '23000') { // violation d'unicité (email déjà utilisé)
        $_SESSION['erreurs_champs'] = ['email' => 'Une demande a déjà été enregistrée avec cette adresse email.'];
        ajouter_message('erreur', 'Votre demande n\'a pas pu être envoyée. Veuillez corriger le champ signalé.');
    } else {
        error_log('Erreur enregistrement inscription : ' . $e->getMessage());
        ajouter_message('erreur', 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer dans quelques instants.');
    }
    rediriger('inscription.php');
}

// ---- Succès ----
unset($_SESSION['anciennes_valeurs'], $_SESSION['erreurs_champs']);
$_SESSION['inscription_reussie'] = [
    'prenom' => $donnees['prenom'],
    'nom'    => $donnees['nom'],
    'email'  => $donnees['email'],
];
rediriger('inscription.php');
