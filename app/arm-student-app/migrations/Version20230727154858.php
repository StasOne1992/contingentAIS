<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230727154858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admission_examination_result ADD admission_examination_subject_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_examination_result ADD CONSTRAINT FK_C28FCD9783A8454 FOREIGN KEY (admission_examination_subject_id) REFERENCES admission_examination_subjects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C28FCD9783A8454 ON admission_examination_result (admission_examination_subject_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admission_examination_result DROP CONSTRAINT FK_C28FCD9783A8454');
        $this->addSql('DROP INDEX IDX_C28FCD9783A8454');
        $this->addSql('ALTER TABLE admission_examination_result DROP admission_examination_subject_id');
    }
}
