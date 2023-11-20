<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230829084009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE access_system_control_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE access_system_control (id INT NOT NULL, student_id INT DEFAULT NULL, access_card_series INT NOT NULL, acces_card_number INT NOT NULL, issue_date DATE NOT NULL, is_locked BOOLEAN DEFAULT NULL, lock_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D33BA65CB944F1A ON access_system_control (student_id)');
        $this->addSql('ALTER TABLE access_system_control ADD CONSTRAINT FK_3D33BA65CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE access_system_control_id_seq CASCADE');
        $this->addSql('ALTER TABLE access_system_control DROP CONSTRAINT FK_3D33BA65CB944F1A');
        $this->addSql('DROP TABLE access_system_control');
    }
}
