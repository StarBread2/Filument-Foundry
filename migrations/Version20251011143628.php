<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011143628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE finish ADD properties LONGTEXT NOT NULL, ADD availability TINYINT(1) NOT NULL, ADD image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE material CHANGE image_path image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP is_admin');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user ADD is_admin TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE material CHANGE image_path image_path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE finish DROP properties, DROP availability, DROP image_path');
    }
}
