-- ============================================================
--  Base de données : Carte Étudiante AERD/Z (Dagana)
--  Compatible MySQL 5.7+ / MariaDB 10.3+
--
--  XAMPP : phpMyAdmin > Importer > choisir ce fichier.
--  Hébergement mutualisé : la création de base est souvent interdite.
--  Dans ce cas, créez la base depuis le panneau de l'hébergeur,
--  SUPPRIMEZ les deux lignes CREATE DATABASE / USE ci-dessous,
--  sélectionnez votre base dans phpMyAdmin puis importez le fichier.
-- ============================================================

CREATE DATABASE IF NOT EXISTS carte_etudiante_dagana
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE carte_etudiante_dagana;

-- ------------------------------------------------------------
--  Administrateurs (mots de passe HACHÉS avec bcrypt, jamais en clair)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS administrateurs (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  identifiant   VARCHAR(50)  NOT NULL,
  mot_de_passe  VARCHAR(255) NOT NULL,
  cree_le       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_identifiant (identifiant)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Étudiants / demandes de carte
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS etudiants (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom               VARCHAR(60)  NOT NULL,
  prenom            VARCHAR(60)  NOT NULL,
  filiere           VARCHAR(100) NOT NULL,
  niveau            ENUM('Licence 1','Licence 2','Licence 3','Master 1','Master 2','Doctorat') NOT NULL,
  telephone         VARCHAR(20)  NOT NULL,
  email             VARCHAR(150) NOT NULL,
  photo             VARCHAR(255) DEFAULT NULL,
  statut            ENUM('en_attente','valide','rejete') NOT NULL DEFAULT 'en_attente',
  numero_carte      VARCHAR(24)  DEFAULT NULL,
  date_delivrance   DATE         DEFAULT NULL,
  date_inscription  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_etudiant_email (email),
  UNIQUE KEY uq_numero_carte (numero_carte),
  KEY idx_statut (statut),
  KEY idx_nom_prenom (nom, prenom),
  KEY idx_date_inscription (date_inscription)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tentatives de connexion ratées (protection contre le brute force)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tentatives_connexion (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip        VARCHAR(45)  NOT NULL,
  cree_le   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ip_date (ip, cree_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Administrateur par défaut
--  Identifiant   : admin
--  Mot de passe  : Dagana@2026!
--  ⚠️ CHANGEZ-LE dès la première connexion (menu « Mot de passe »).
-- ------------------------------------------------------------
INSERT IGNORE INTO administrateurs (identifiant, mot_de_passe) VALUES
  ('admin', '$2y$10$EbrO0YJSwJyhktoMujhjh.j/F.DFc228P5tZgZCYwZkI5mWuZWzTe');
