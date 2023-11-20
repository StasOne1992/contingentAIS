<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230727155416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abiturient_petition ADD admission_plan_position_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD CONSTRAINT FK_50A0C6E54452387F FOREIGN KEY (admission_plan_position_id) REFERENCES admission_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_50A0C6E54452387F ON abiturient_petition (admission_plan_position_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE abiturient_petition DROP CONSTRAINT FK_50A0C6E54452387F');
        $this->addSql('DROP INDEX IDX_50A0C6E54452387F');
        $this->addSql('ALTER TABLE abiturient_petition DROP admission_plan_position_id');
    }
}
