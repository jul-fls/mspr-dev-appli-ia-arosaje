<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230608145255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guarding ADD CONSTRAINT FK_9A0CC43E1D935652 FOREIGN KEY (plant_id) REFERENCES plant (id)');
        $this->addSql('ALTER TABLE guarding ADD CONSTRAINT FK_9A0CC43E11CC8B0A FOREIGN KEY (guardian_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE plant ADD CONSTRAINT FK_AB030D727E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user DROP phone, DROP address_number, DROP address_street');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('ALTER TABLE user ADD phone VARCHAR(10) DEFAULT NULL, ADD address_number INT DEFAULT NULL, ADD address_street VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE plant DROP FOREIGN KEY FK_AB030D727E3C61F9');
        $this->addSql('ALTER TABLE guarding DROP FOREIGN KEY FK_9A0CC43E1D935652');
        $this->addSql('ALTER TABLE guarding DROP FOREIGN KEY FK_9A0CC43E11CC8B0A');
    }
}
