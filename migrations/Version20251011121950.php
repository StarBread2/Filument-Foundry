<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251011121950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE properties (id INT AUTO_INCREMENT NOT NULL, properties LONGTEXT NOT NULL, availability TINYINT(1) NOT NULL, image_path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, address LONGTEXT NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE color ADD image_path VARCHAR(255) NOT NULL, ADD availability TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE material ADD properties LONGTEXT NOT NULL, ADD availability TINYINT(1) NOT NULL, ADD image_path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user_order ADD user_id INT NOT NULL, ADD file_path VARCHAR(255) NOT NULL, ADD order_state VARCHAR(255) NOT NULL, ADD delievery_date DATE NOT NULL, ADD delivery_arrival DATE NOT NULL, ADD price_total NUMERIC(10, 2) NOT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_order ADD CONSTRAINT FK_17EB68C0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_17EB68C0A76ED395 ON user_order (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_order DROP FOREIGN KEY FK_17EB68C0A76ED395');
        $this->addSql('DROP TABLE properties');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE color DROP image_path, DROP availability');
        $this->addSql('DROP INDEX IDX_17EB68C0A76ED395 ON user_order');
        $this->addSql('ALTER TABLE user_order DROP user_id, DROP file_path, DROP order_state, DROP delievery_date, DROP delivery_arrival, DROP price_total, DROP created_at');
        $this->addSql('ALTER TABLE material DROP properties, DROP availability, DROP image_path');
    }
}
