-- ============================================================
-- Migration V1.1 — Club de Lecture
-- À importer dans phpMyAdmin APRÈS le dump initial
-- Toutes les instructions sont idempotentes (IF NOT EXISTS)
-- ============================================================

-- Ajouter book_id à comments pour lier les commentaires directement
-- aux livres (au lieu de passer par readings)
ALTER TABLE `comments`
    ADD COLUMN `book_id` INT DEFAULT NULL AFTER `id`;

ALTER TABLE `comments`
    ADD KEY `book_id` (`book_id`);

-- --------------------------------------------------------
-- Table reviews (avis : note 1-5 + commentaire + modération)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `book_id`     INT          NOT NULL,
  `user_id`     INT          DEFAULT NULL,
  `note`        TINYINT      NOT NULL,
  `commentaire` TEXT         COLLATE utf8mb4_general_ci,
  `hidden`      TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review` (`book_id`, `user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table progress (progression 0-100 par user et par livre)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `progress` (
  `id`          INT      NOT NULL AUTO_INCREMENT,
  `book_id`     INT      NOT NULL,
  `user_id`     INT      DEFAULT NULL,
  `pourcentage` TINYINT  NOT NULL DEFAULT 0,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_progress` (`book_id`, `user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table sessions (planification lives / rencontres)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `book_id`     INT          NOT NULL,
  `titre`       VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date_heure`  DATETIME     NOT NULL,
  `lien`        VARCHAR(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lieu`        VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` TEXT         COLLATE utf8mb4_general_ci,
  `created_by`  INT          DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table session_attendance (inscriptions aux sessions)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `session_attendance` (
  `id`         INT      NOT NULL AUTO_INCREMENT,
  `session_id` INT      NOT NULL,
  `user_id`    INT      NOT NULL,
  `statut`     ENUM('inscrit','present','absent') DEFAULT 'inscrit',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance` (`session_id`, `user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
