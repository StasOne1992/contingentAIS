<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230815172055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contingent_document DROP is_payed');
        $this->addSql('ALTER TABLE student ADD abiturient_petition_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33821E040B FOREIGN KEY (abiturient_petition_id) REFERENCES abiturient_petition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B723AF33821E040B ON student (abiturient_petition_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE contingent_document ADD is_payed BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE student DROP CONSTRAINT FK_B723AF33821E040B');
        $this->addSql('DROP INDEX IDX_B723AF33821E040B');
        $this->addSql('ALTER TABLE student DROP abiturient_petition_id');
    }
}
