<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Version20160403163551 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE book (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, slug VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, pages INT UNSIGNED NOT NULL, UNIQUE INDEX UNIQ_CBE5A331989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT UNSIGNED AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, password CHAR(64) NOT NULL, email VARCHAR(255) NOT NULL, roles VARCHAR(255) NOT NULL, token CHAR(21) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE user');
    }
}
