<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230804112428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abiturient_petition ADD has_target_agreement BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD target_agreement_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD target_agreement_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD target_agreement_employer TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE abiturient_petition DROP has_target_agreement');
        $this->addSql('ALTER TABLE abiturient_petition DROP target_agreement_number');
        $this->addSql('ALTER TABLE abiturient_petition DROP target_agreement_date');
        $this->addSql('ALTER TABLE abiturient_petition DROP target_agreement_employer');
    }
}
