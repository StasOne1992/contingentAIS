<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230828092830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admission ADD college_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission ADD CONSTRAINT FK_F4BB024A770124B2 FOREIGN KEY (college_id) REFERENCES college (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F4BB024A770124B2 ON admission (college_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admission DROP CONSTRAINT FK_F4BB024A770124B2');
        $this->addSql('DROP INDEX IDX_F4BB024A770124B2');
        $this->addSql('ALTER TABLE admission DROP college_id');
    }
}
