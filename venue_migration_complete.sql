-- =====================================================
-- MIGRATION SQL COMPLÈTE POUR L'ENTITÉ VENUE
-- Base de données: MySQL (XAMPP)
-- =====================================================
-- 
-- Ce script crée la table venue et ajoute la relation
-- avec la table event. La capacité du venue détermine
-- le nombre de tickets disponibles pour les événements.
--
-- INSTRUCTIONS:
-- 1. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Sélectionnez votre base de données
-- 3. Allez dans l'onglet "SQL"
-- 4. Copiez-collez ce script complet
-- 5. Cliquez sur "Exécuter"
-- =====================================================

-- =====================================================
-- ÉTAPE 1: Créer la table venue
-- =====================================================
CREATE TABLE IF NOT EXISTS `venue` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `adresse` VARCHAR(255) NOT NULL,
  `capacity` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `IDX_VENUE_NAME` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ÉTAPE 2: Ajouter la colonne venue_id dans la table event
-- =====================================================
-- Vérifier si la colonne n'existe pas déjà avant de l'ajouter
SET @dbname = DATABASE();
SET @tablename = 'event';
SET @columnname = 'venue_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD `', @columnname, '` INT DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- ÉTAPE 3: Ajouter l'index pour améliorer les performances
-- =====================================================
-- Vérifier si l'index n'existe pas déjà
SET @indexname = 'IDX_EVENT_VENUE';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`venue_id`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- ÉTAPE 4: Ajouter la contrainte de clé étrangère
-- =====================================================
-- Supprimer la contrainte si elle existe déjà
SET @constraintname = 'FK_EVENT_VENUE';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @constraintname)
  ) > 0,
  CONCAT('ALTER TABLE `', @tablename, '` DROP FOREIGN KEY `', @constraintname, '`'),
  'SELECT 1'
));
PREPARE dropIfExists FROM @preparedStatement;
EXECUTE dropIfExists;
DEALLOCATE PREPARE dropIfExists;

-- Ajouter la contrainte de clé étrangère
ALTER TABLE `event` 
ADD CONSTRAINT `FK_EVENT_VENUE` 
FOREIGN KEY (`venue_id`) 
REFERENCES `venue` (`id`) 
ON DELETE SET NULL
ON UPDATE CASCADE;

-- =====================================================
-- ÉTAPE 5: Données d'exemple (optionnel)
-- =====================================================
-- Vous pouvez décommenter ces lignes pour insérer des exemples de venues

-- INSERT INTO `venue` (`name`, `adresse`, `capacity`) VALUES
-- ('Stade de France', '93200 Saint-Denis, France', 80000),
-- ('Olympia', '28 Boulevard des Capucines, 75009 Paris', 2000),
-- ('Palais des Congrès', '2 Place de la Porte Maillot, 75017 Paris', 3700),
-- ('Zénith de Paris', '211 Avenue Jean Jaurès, 75019 Paris', 6200),
-- ('Accor Arena', '8 Boulevard de Bercy, 75012 Paris', 20000);

-- =====================================================
-- VÉRIFICATION
-- =====================================================
-- Vérifier que la table venue a été créée
SELECT 'Table venue créée avec succès!' AS Message;

-- Vérifier que la colonne venue_id a été ajoutée à event
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'event'
  AND COLUMN_NAME = 'venue_id';

-- Afficher la structure de la table venue
DESCRIBE `venue`;

-- =====================================================
-- FIN DU SCRIPT
-- =====================================================

