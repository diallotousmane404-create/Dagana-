<?php
require_once __DIR__ . '/includes/fonctions.php';

$titrePage   = 'Accueil';
$pageActive  = 'accueil';
require __DIR__ . '/includes/entete.php';
?>

<!-- ===== Bannière d'accueil ===== -->
<section class="hero">
    <div class="conteneur hero-grille">
        <div class="hero-texte">
            <p class="badge-hero">Inscription en ligne</p>
            <h1>Votre carte étudiante, simplement.</h1>
            <p class="hero-intro">
                Étudiants de Dagana : déposez votre demande de carte étudiante depuis votre téléphone
                ou votre ordinateur. Remplissez le formulaire, l'administration vérifie votre dossier
                puis édite votre carte.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primaire btn-grand" href="inscription.php">S'inscrire maintenant</a>
                <a class="btn btn-clair btn-grand" href="#etapes">Comment ça marche ?</a>
            </div>
        </div>

        <!-- Aperçu décoratif d'une carte (données fictives) -->
        <div class="hero-visuel" aria-hidden="true">
            <div class="carte-cadre">
                <div class="carte">
                    <div class="carte-haut">
                        <img class="carte-logo" src="<?= e(LOGO) ?>" alt="">
                        <div>
                            <strong>CARTE D'ÉTUDIANT</strong>
                            <span><?= e(SIGLE) ?> · Dagana</span>
                        </div>
                    </div>
                    <div class="carte-corps">
                        <div class="carte-photo"><span class="carte-initiales">AD</span></div>
                        <div class="carte-infos">
                            <p class="carte-nom">DIOP</p>
                            <p class="carte-prenom">Aminata</p>
                            <dl>
                                <dt>Filière</dt><dd>Mathématiques</dd>
                                <dt>Niveau</dt><dd>Licence 2</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="carte-bas">
                        <span>N° <?= e(PREFIXE_CARTE) ?>-<?= e(date('Y')) ?>-00001</span>
                        <span>Exemple</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== Étapes ===== -->
<section class="section" id="etapes">
    <div class="conteneur">
        <h2 class="titre-section">Comment obtenir sa carte ?</h2>
        <p class="sous-titre-section">Quatre étapes, sans déplacement pour déposer votre demande.</p>

        <ol class="grille-etapes">
            <li class="etape">
                <span class="etape-num">1</span>
                <h3>Remplissez le formulaire</h3>
                <p>Nom, prénom, filière, niveau d'études, téléphone, email et photo (facultative).</p>
            </li>
            <li class="etape">
                <span class="etape-num">2</span>
                <h3>Vérification</h3>
                <p>L'administration contrôle les informations de votre dossier.</p>
            </li>
            <li class="etape">
                <span class="etape-num">3</span>
                <h3>Validation</h3>
                <p>Une fois votre demande validée, un numéro de carte unique vous est attribué.</p>
            </li>
            <li class="etape">
                <span class="etape-num">4</span>
                <h3>Votre carte est prête</h3>
                <p>Votre carte est éditée avec votre nom, votre filière, votre niveau et votre photo.</p>
            </li>
        </ol>
    </div>
</section>

<!-- ===== Avantages ===== -->
<section class="section section-alt">
    <div class="conteneur">
        <h2 class="titre-section">Une plateforme pensée pour les étudiants</h2>
        <div class="grille-avantages">
            <article class="avantage">
                <span class="avantage-icone" aria-hidden="true">📱</span>
                <h3>Sur téléphone ou ordinateur</h3>
                <p>Le formulaire s'adapte à tous les écrans, pour s'inscrire où que vous soyez.</p>
            </article>
            <article class="avantage">
                <span class="avantage-icone" aria-hidden="true">🔒</span>
                <h3>Données protégées</h3>
                <p>Vos informations sont utilisées uniquement pour établir votre carte étudiante.</p>
            </article>
            <article class="avantage">
                <span class="avantage-icone" aria-hidden="true">✅</span>
                <h3>Vérifications immédiates</h3>
                <p>Le formulaire signale tout de suite les erreurs de saisie pour vous éviter de recommencer.</p>
            </article>
            <article class="avantage">
                <span class="avantage-icone" aria-hidden="true">🎓</span>
                <h3>Tous les niveaux</h3>
                <p>De la Licence 1 au Doctorat : Licence, Master et Doctorat sont acceptés.</p>
            </article>
        </div>
    </div>
</section>

<!-- ===== À préparer ===== -->
<section class="section">
    <div class="conteneur deux-colonnes">
        <div>
            <h2 class="titre-section titre-gauche">À préparer avant de vous inscrire</h2>
            <ul class="liste-coches">
                <li>Votre nom, prénom, filière et niveau d'études</li>
                <li>Un numéro de téléphone joignable</li>
                <li>Une adresse email valide</li>
                <li>Une photo d'identité récente, de face <em>(facultative, JPG, PNG ou WebP, 2 Mo maximum)</em></li>
            </ul>
            <p><a class="btn btn-primaire" href="inscription.php">Commencer mon inscription</a></p>
        </div>

        <div>
            <h2 class="titre-section titre-gauche">Questions fréquentes</h2>
            <div class="faq">
                <details>
                    <summary>Qui peut s'inscrire ?</summary>
                    <p>Tout étudiant de Dagana en Licence, Master ou Doctorat.</p>
                </details>
                <details>
                    <summary>La photo est-elle obligatoire ?</summary>
                    <p>Non, elle est facultative. Elle est toutefois recommandée : si vous la fournissez, elle figurera sur votre carte.</p>
                </details>
                <details>
                    <summary>Comment savoir si ma demande est validée ?</summary>
                    <p>L'administration examine chaque demande et peut vous contacter par téléphone ou par email.</p>
                </details>
                <details>
                    <summary>J'ai fait une erreur dans mes informations, que faire ?</summary>
                    <p>Écrivez à l'administration à l'adresse <a href="mailto:<?= e(EMAIL_CONTACT) ?>"><?= e(EMAIL_CONTACT) ?></a> en précisant les informations à corriger.</p>
                </details>
            </div>
        </div>
    </div>
</section>

<!-- ===== Appel à l'action ===== -->
<section class="bandeau-cta">
    <div class="conteneur">
        <h2>Prêt à demander votre carte étudiante ?</h2>
        <p>Cela ne prend que quelques minutes.</p>
        <a class="btn btn-clair btn-grand" href="inscription.php">Je m'inscris</a>
    </div>
</section>

<?php require __DIR__ . '/includes/pied.php'; ?>
