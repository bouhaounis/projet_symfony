-- Migration SQL pour l'entité Venue
-- Exécutez ce script dans votre base de données MySQL

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
ON DELETE SET NULL;

