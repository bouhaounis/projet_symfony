<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to update user table: add role column and remove roles if exists
 */
final class Version20251214170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update user table: add role and isverified columns';
    }

    public function up(Schema $schema): void
    {
        // Add role column if it doesn't exist
        $this->addSql("
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'user' 
                AND COLUMN_NAME = 'role');
            SET @sqlstmt := IF(@exist = 0, 
                \"ALTER TABLE `user` ADD COLUMN `role` ENUM('admin','user') NOT NULL DEFAULT 'user' AFTER `password`\", 
                'SELECT 1');
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");
        
        // Add isverified column if it doesn't exist
        $this->addSql("
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'user' 
                AND COLUMN_NAME = 'isverified');
            SET @sqlstmt := IF(@exist = 0, 
                'ALTER TABLE `user` ADD COLUMN `isverified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `role`', 
                'SELECT 1');
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");
        
        // Try to drop roles column if it exists
        $this->addSql("
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'user' 
                AND COLUMN_NAME = 'roles');
            SET @sqlstmt := IF(@exist > 0, 
                'ALTER TABLE `user` DROP COLUMN `roles`', 
                'SELECT 1');
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP COLUMN IF EXISTS `role`');
        $this->addSql('ALTER TABLE `user` DROP COLUMN IF EXISTS `isverified`');
    }
}
