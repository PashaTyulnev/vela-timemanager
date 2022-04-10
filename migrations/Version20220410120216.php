<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220410120216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_app_settings (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, auth_only_by_pin TINYINT(1) NOT NULL, auto_checkout_after_hours INT DEFAULT NULL, auto_checkout_give_hours INT NOT NULL, INDEX IDX_13080AA6979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_main_user (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, company_object_id INT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_5E3B6EDB979B1AD6 (company_id), INDEX IDX_5E3B6EDB1036DCDB (company_object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_object (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_user (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, pin VARCHAR(17) DEFAULT NULL, INDEX IDX_CEFECCA7979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_entry (id INT AUTO_INCREMENT NOT NULL, time_entry_type_id INT DEFAULT NULL, employer_id INT NOT NULL, company_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', auto_check_out TINYINT(1) DEFAULT NULL, INDEX IDX_6E537C0C617B718B (time_entry_type_id), INDEX IDX_6E537C0C41CD9E7A (employer_id), INDEX IDX_6E537C0C979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_entry_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_app_settings ADD CONSTRAINT FK_13080AA6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company_main_user ADD CONSTRAINT FK_5E3B6EDB979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company_main_user ADD CONSTRAINT FK_5E3B6EDB1036DCDB FOREIGN KEY (company_object_id) REFERENCES company_object (id)');
        $this->addSql('ALTER TABLE company_user ADD CONSTRAINT FK_CEFECCA7979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0C617B718B FOREIGN KEY (time_entry_type_id) REFERENCES time_entry_type (id)');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0C41CD9E7A FOREIGN KEY (employer_id) REFERENCES company_user (id)');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0C979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_app_settings DROP FOREIGN KEY FK_13080AA6979B1AD6');
        $this->addSql('ALTER TABLE company_main_user DROP FOREIGN KEY FK_5E3B6EDB979B1AD6');
        $this->addSql('ALTER TABLE company_user DROP FOREIGN KEY FK_CEFECCA7979B1AD6');
        $this->addSql('ALTER TABLE time_entry DROP FOREIGN KEY FK_6E537C0C979B1AD6');
        $this->addSql('ALTER TABLE company_main_user DROP FOREIGN KEY FK_5E3B6EDB1036DCDB');
        $this->addSql('ALTER TABLE time_entry DROP FOREIGN KEY FK_6E537C0C41CD9E7A');
        $this->addSql('ALTER TABLE time_entry DROP FOREIGN KEY FK_6E537C0C617B718B');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_app_settings');
        $this->addSql('DROP TABLE company_main_user');
        $this->addSql('DROP TABLE company_object');
        $this->addSql('DROP TABLE company_user');
        $this->addSql('DROP TABLE time_entry');
        $this->addSql('DROP TABLE time_entry_type');
    }
}
