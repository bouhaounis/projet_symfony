-- =====================================================
-- Migration : Ajout de user_id dans la table booking
-- Compatible MySQL (XAMPP)
-- Phase 1 - Sécurité
-- =====================================================

-- Étape 1 : Ajouter la colonne user_id (permettant NULL temporairement pour données existantes)
ALTER TABLE `booking` ADD `user_id` INT DEFAULT NULL AFTER `created_at`;

-- Étape 2 : Si vous avez des bookings existants, les associer au premier utilisateur
-- DÉCOMMENTEZ ET ADAPTEZ SI NÉCESSAIRE :
-- UPDATE `booking` SET `user_id` = (SELECT id FROM `user` LIMIT 1) WHERE `user_id` IS NULL;

-- Étape 3 : Rendre la colonne obligatoire (après avoir associé les données existantes)
-- ALTER TABLE `booking` MODIFY `user_id` INT NOT NULL;

-- Étape 4 : Ajouter l'index pour améliorer les performances
ALTER TABLE `booking` ADD INDEX `IDX_BOOKING_USER` (`user_id`);

-- Étape 5 : Ajouter la contrainte de clé étrangère
ALTER TABLE `booking` ADD CONSTRAINT `FK_BOOKING_USER` 
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

-- =====================================================
-- Vérification
-- =====================================================
-- Vérifier la structure :
-- DESCRIBE booking;

-- Vérifier les contraintes :
-- SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
-- WHERE TABLE_NAME = 'booking' AND COLUMN_NAME = 'user_id';

-- =====================================================
-- Rollback (si nécessaire)
-- =====================================================
-- ALTER TABLE `booking` DROP FOREIGN KEY `FK_BOOKING_USER`;
-- ALTER TABLE `booking` DROP INDEX `IDX_BOOKING_USER`;
-- ALTER TABLE `booking` DROP COLUMN `user_id`;

