<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to create event table
 */
final class Version20251214180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create event table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `event` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image VARCHAR(255) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description VARCHAR(255),
            date_event DATE NOT NULL,
            capacity INT NOT NULL,
            prix FLOAT NOT NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `event`');
    }
}
