<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251215133900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add event_id foreign key to booking table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `booking` ADD `event_id` INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `booking` ADD INDEX `IDX_BOOKING_EVENT` (`event_id`)');
        $this->addSql('ALTER TABLE `booking` ADD CONSTRAINT `FK_BOOKING_EVENT` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `booking` DROP FOREIGN KEY `FK_BOOKING_EVENT`');
        $this->addSql('ALTER TABLE `booking` DROP INDEX `IDX_BOOKING_EVENT`');
        $this->addSql('ALTER TABLE `booking` DROP COLUMN `event_id`');
    }
}
