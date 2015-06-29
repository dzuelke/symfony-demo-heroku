<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150629223845 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE Comments_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE Posts_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE Users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE Comments (id INT NOT NULL, post_id INT NOT NULL, content TEXT NOT NULL, authorEmail VARCHAR(255) NOT NULL, publishedAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A6E8F47C4B89032C ON Comments (post_id)');
        $this->addSql('CREATE TABLE Posts (id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, summary TEXT NOT NULL, content TEXT NOT NULL, authorEmail VARCHAR(255) NOT NULL, publishedAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE Users (id INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D5428AEDF85E0677 ON Users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D5428AEDE7927C74 ON Users (email)');
        $this->addSql('ALTER TABLE Comments ADD CONSTRAINT FK_A6E8F47C4B89032C FOREIGN KEY (post_id) REFERENCES Posts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE Comments DROP CONSTRAINT FK_A6E8F47C4B89032C');
        $this->addSql('DROP SEQUENCE Comments_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE Posts_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE Users_id_seq CASCADE');
        $this->addSql('DROP TABLE Comments');
        $this->addSql('DROP TABLE Posts');
        $this->addSql('DROP TABLE Users');
    }
}
