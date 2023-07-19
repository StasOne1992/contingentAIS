<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230717151912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE regions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE regions (id INT NOT NULL, name VARCHAR(255) NOT NULL, code INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE abiturient_petition ADD region_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD can_pay BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD school_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD education_document_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD education_document_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD have_error_in_petition BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD number_provision INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD date_provision DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD number_cancel INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD date_cancel DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD cancel_reason TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD is_orphan BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD is_invalid BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD is_poor BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD need_student_accommondation BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD attaches JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD birth_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ADD lock_update_form_vis BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE abiturient_petition ALTER local_number TYPE INT');
        $this->addSql('ALTER TABLE abiturient_petition ADD CONSTRAINT FK_50A0C6E598260155 FOREIGN KEY (region_id) REFERENCES regions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_50A0C6E598260155 ON abiturient_petition (region_id)');
        $this->addSql('ALTER TABLE admission ALTER date_start DROP NOT NULL');
        $this->addSql('ALTER TABLE admission ALTER date_end DROP NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER available_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER delivered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE abiturient_petition DROP CONSTRAINT FK_50A0C6E598260155');
        $this->addSql('DROP SEQUENCE regions_id_seq CASCADE');
        $this->addSql('DROP TABLE regions');
        $this->addSql('ALTER TABLE admission ALTER date_start SET NOT NULL');
        $this->addSql('ALTER TABLE admission ALTER date_end SET NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER available_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER delivered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS NULL');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS NULL');
        $this->addSql('DROP INDEX IDX_50A0C6E598260155');
        $this->addSql('ALTER TABLE abiturient_petition DROP region_id');
        $this->addSql('ALTER TABLE abiturient_petition DROP can_pay');
        $this->addSql('ALTER TABLE abiturient_petition DROP school_name');
        $this->addSql('ALTER TABLE abiturient_petition DROP education_document_name');
        $this->addSql('ALTER TABLE abiturient_petition DROP education_document_number');
        $this->addSql('ALTER TABLE abiturient_petition DROP have_error_in_petition');
        $this->addSql('ALTER TABLE abiturient_petition DROP number_provision');
        $this->addSql('ALTER TABLE abiturient_petition DROP date_provision');
        $this->addSql('ALTER TABLE abiturient_petition DROP number_cancel');
        $this->addSql('ALTER TABLE abiturient_petition DROP date_cancel');
        $this->addSql('ALTER TABLE abiturient_petition DROP cancel_reason');
        $this->addSql('ALTER TABLE abiturient_petition DROP is_orphan');
        $this->addSql('ALTER TABLE abiturient_petition DROP is_invalid');
        $this->addSql('ALTER TABLE abiturient_petition DROP is_poor');
        $this->addSql('ALTER TABLE abiturient_petition DROP need_student_accommondation');
        $this->addSql('ALTER TABLE abiturient_petition DROP attaches');
        $this->addSql('ALTER TABLE abiturient_petition DROP birth_date');
        $this->addSql('ALTER TABLE abiturient_petition DROP lock_update_form_vis');
        $this->addSql('ALTER TABLE abiturient_petition ALTER local_number TYPE VARCHAR(255)');
    }
}
