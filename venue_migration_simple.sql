-- =====================================================
-- MIGRATION SQL SIMPLE POUR L'ENTITÉ VENUE
-- Base de données: MySQL (XAMPP)
-- =====================================================
-- 
-- Version simplifiée sans vérifications
-- Utilisez cette version si vous êtes sûr que les tables n'existent pas
-- =====================================================

-- 1. Créer la table venue
CREATE TABLE `venue` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `adresse` VARCHAR(255) NOT NULL,
  `capacity` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ajouter la colonne venue_id dans la table event
ALTER TABLE `event` 
ADD `venue_id` INT DEFAULT NULL;

-- 3. Ajouter l'index pour améliorer les performances
ALTER TABLE `event` 
ADD INDEX `IDX_EVENT_VENUE` (`venue_id`);

-- 4. Ajouter la contrainte de clé étrangère
ALTER TABLE `event` 
ADD CONSTRAINT `FK_EVENT_VENUE` 
FOREIGN KEY (`venue_id`) 
REFERENCES `venue` (`id`) 
ON DELETE SET NULL
ON UPDATE CASCADE;

-- =====================================================
-- Données d'exemple (optionnel - décommentez si besoin)
-- =====================================================
-- INSERT INTO `venue` (`name`, `adresse`, `capacity`) VALUES
-- ('Stade de France', '93200 Saint-Denis, France', 80000),
-- ('Olympia', '28 Boulevard des Capucines, 75009 Paris', 2000),
-- ('Palais des Congrès', '2 Place de la Porte Maillot, 75017 Paris', 3700),
-- ('Zénith de Paris', '211 Avenue Jean Jaurès, 75019 Paris', 6200),
-- ('Accor Arena', '8 Boulevard de Bercy, 75012 Paris', 20000);

