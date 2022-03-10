<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220301192336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE time_entry DROP FOREIGN KEY FK_6E537C0CA76ED395');
        $this->addSql('DROP INDEX IDX_6E537C0CA76ED395 ON time_entry');
        $this->addSql('ALTER TABLE time_entry ADD time_entry_type_id INT DEFAULT NULL, DROP user_id');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0C617B718B FOREIGN KEY (time_entry_type_id) REFERENCES time_entry_type (id)');
        $this->addSql('CREATE INDEX IDX_6E537C0C617B718B ON time_entry (time_entry_type_id)');
        $this->addSql('ALTER TABLE time_entry_type DROP FOREIGN KEY FK_A06603D71EB30A8E');
        $this->addSql('DROP INDEX IDX_A06603D71EB30A8E ON time_entry_type');
        $this->addSql('ALTER TABLE time_entry_type DROP time_entry_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin CHANGE email email VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE employer CHANGE first_name first_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE last_name last_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE pin pin VARCHAR(17) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE time_entry DROP FOREIGN KEY FK_6E537C0C617B718B');
        $this->addSql('DROP INDEX IDX_6E537C0C617B718B ON time_entry');
        $this->addSql('ALTER TABLE time_entry ADD user_id INT NOT NULL, DROP time_entry_type_id');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0CA76ED395 FOREIGN KEY (user_id) REFERENCES employer (id)');
        $this->addSql('CREATE INDEX IDX_6E537C0CA76ED395 ON time_entry (user_id)');
        $this->addSql('ALTER TABLE time_entry_type ADD time_entry_id INT DEFAULT NULL, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE time_entry_type ADD CONSTRAINT FK_A06603D71EB30A8E FOREIGN KEY (time_entry_id) REFERENCES time_entry (id)');
        $this->addSql('CREATE INDEX IDX_A06603D71EB30A8E ON time_entry_type (time_entry_id)');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
