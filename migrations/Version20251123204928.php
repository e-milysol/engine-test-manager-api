<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123204928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE test_request (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(32) NOT NULL, priority SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE test_run (id INT AUTO_INCREMENT NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, result VARCHAR(32) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, no VARCHAR(33) DEFAULT NULL, test_request_id INT DEFAULT NULL, INDEX IDX_290B7807394D0E9C (test_request_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE test_run ADD CONSTRAINT FK_290B7807394D0E9C FOREIGN KEY (test_request_id) REFERENCES test_request (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_run DROP FOREIGN KEY FK_290B7807394D0E9C');
        $this->addSql('DROP TABLE test_request');
        $this->addSql('DROP TABLE test_run');
    }
}
