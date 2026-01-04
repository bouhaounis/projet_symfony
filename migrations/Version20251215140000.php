<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter la relation User dans Booking
 * Compatible MySQL (XAMPP)
 */
final class Version20251215140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id foreign key to booking table for security - Phase 1';
    }

    public function up(Schema $schema): void
    {
        // Étape 1 : Ajouter la colonne user_id (permettant NULL temporairement pour données existantes)
        $this->addSql('ALTER TABLE `booking` ADD `user_id` INT DEFAULT NULL AFTER `created_at`');
        
        // Étape 2 : Associer les bookings existants au premier utilisateur (si nécessaire)
        // Note: Cette étape peut être commentée si vous n'avez pas de données existantes
        // ou si vous préférez les associer manuellement
        $this->addSql('UPDATE `booking` SET `user_id` = (SELECT id FROM `user` LIMIT 1) WHERE `user_id` IS NULL');
        
        // Étape 3 : Rendre la colonne obligatoire (après avoir associé les données)
        $this->addSql('ALTER TABLE `booking` MODIFY `user_id` INT NOT NULL');
        
        // Étape 4 : Ajouter l'index pour améliorer les performances
        $this->addSql('ALTER TABLE `booking` ADD INDEX `IDX_BOOKING_USER` (`user_id`)');
        
        // Étape 5 : Ajouter la contrainte de clé étrangère
        $this->addSql('ALTER TABLE `booking` ADD CONSTRAINT `FK_BOOKING_USER` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la contrainte de clé étrangère
        $this->addSql('ALTER TABLE `booking` DROP FOREIGN KEY `FK_BOOKING_USER`');
        
        // Supprimer l'index
        $this->addSql('ALTER TABLE `booking` DROP INDEX `IDX_BOOKING_USER`');
        
        // Supprimer la colonne user_id
        $this->addSql('ALTER TABLE `booking` DROP COLUMN `user_id`');
    }
}

