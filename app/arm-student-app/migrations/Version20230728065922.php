<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230728065922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admission_examination_result DROP CONSTRAINT fk_c28fcd9783a8454');
        $this->addSql('DROP INDEX idx_c28fcd9783a8454');
        $this->addSql('ALTER TABLE admission_examination_result RENAME COLUMN admission_examination_subject_id TO admission_examination_id');
        $this->addSql('ALTER TABLE admission_examination_result ADD CONSTRAINT FK_C28FCD94D62F206 FOREIGN KEY (admission_examination_id) REFERENCES admission_examination (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C28FCD94D62F206 ON admission_examination_result (admission_examination_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admission_examination_result DROP CONSTRAINT FK_C28FCD94D62F206');
        $this->addSql('DROP INDEX IDX_C28FCD94D62F206');
        $this->addSql('ALTER TABLE admission_examination_result RENAME COLUMN admission_examination_id TO admission_examination_subject_id');
        $this->addSql('ALTER TABLE admission_examination_result ADD CONSTRAINT fk_c28fcd9783a8454 FOREIGN KEY (admission_examination_subject_id) REFERENCES admission_examination_subjects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c28fcd9783a8454 ON admission_examination_result (admission_examination_subject_id)');
    }
}
