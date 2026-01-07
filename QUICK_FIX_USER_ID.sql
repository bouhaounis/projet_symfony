-- =====================================================
-- QUICK FIX : Ajout de user_id dans booking
-- Exécutez ce script dans phpMyAdmin (XAMPP)
-- =====================================================

-- Étape 1 : Ajouter la colonne user_id (NULL autorisé temporairement)
ALTER TABLE `booking` ADD `user_id` INT DEFAULT NULL AFTER `created_at`;

-- Étape 2 : Si vous avez des bookings existants, les associer au premier utilisateur
-- (Décommentez la ligne suivante si vous avez des données existantes)
UPDATE `booking` SET `user_id` = (SELECT id FROM `user` LIMIT 1) WHERE `user_id` IS NULL;

-- Étape 3 : Rendre la colonne obligatoire
ALTER TABLE `booking` MODIFY `user_id` INT NOT NULL;

-- Étape 4 : Ajouter l'index
ALTER TABLE `booking` ADD INDEX `IDX_BOOKING_USER` (`user_id`);

-- Étape 5 : Ajouter la clé étrangère
ALTER TABLE `booking` ADD CONSTRAINT `FK_BOOKING_USER` 
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;


