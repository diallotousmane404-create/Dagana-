<?php
/**
 * Configuration générale du site.
 * Modifiez uniquement les valeurs de ce fichier (jamais le reste du code).
 */

// ---------- Base de données MySQL ----------
const DB_HOTE         = 'localhost';
const DB_NOM          = 'carte_etudiante_dagana';
const DB_UTILISATEUR  = 'root';   // XAMPP : root
const DB_MOT_DE_PASSE = '';       // XAMPP : vide par défaut. EN LIGNE : mettez le mot de passe fourni par l'hébergeur
const DB_CHARSET      = 'utf8mb4';

// ---------- Identité du site ----------
const NOM_SITE      = 'Carte Étudiante AERD/Z';
const SIGLE         = 'AERD/Z';
const NOM_ORGANISME = "Amicale des Étudiants Ressortissants de Dagana à l'UASZ";
const EMAIL_CONTACT = 'contact@exemple.sn';   // À remplacer par votre vraie adresse
const PREFIXE_CARTE = 'AERDZ';                // Les cartes seront du type AERDZ-2026-00012
const LOGO          = 'assets/img/logo.jpg';

// ---------- Photos des étudiants ----------
const TAILLE_MAX_PHOTO = 2097152;                          // 2 Mo
const DOSSIER_PHOTOS   = __DIR__ . '/../uploads/photos/';
const URL_PHOTOS       = 'uploads/photos/';

// ---------- Divers ----------
const MODE_DEBUG              = false;   // true uniquement en local (affiche les erreurs PHP)
const DUREE_SESSION_ADMIN     = 1800;    // déconnexion automatique après 30 min d'inactivité
const MOT_DE_PASSE_PAR_DEFAUT = 'Dagana@2026!';   // sert uniquement à avertir l'admin de le changer
const NIVEAUX = ['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2', 'Doctorat'];

// Hachage factice : permet de garder un temps de réponse identique si l'identifiant n'existe pas
const HASH_FACTICE = '$2y$10$XGSpRNIw2012iBXtZE4VQeJko5uVSkvAso985.//Y3OKzD4G3gLsi';

date_default_timezone_set('Africa/Dakar');
