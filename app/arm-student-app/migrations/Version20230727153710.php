<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230727153710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE admission_examination_result_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE admission_examination_result (id INT NOT NULL, abiturient_petition_id INT DEFAULT NULL, mark DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C28FCD9821E040B ON admission_examination_result (abiturient_petition_id)');
        $this->addSql('ALTER TABLE admission_examination_result ADD CONSTRAINT FK_C28FCD9821E040B FOREIGN KEY (abiturient_petition_id) REFERENCES abiturient_petition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE admission_examination_result_id_seq CASCADE');
        $this->addSql('ALTER TABLE admission_examination_result DROP CONSTRAINT FK_C28FCD9821E040B');
        $this->addSql('DROP TABLE admission_examination_result');
    }
}
