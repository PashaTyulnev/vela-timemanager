<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220519164631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_main_user_role DROP FOREIGN KEY FK_23964EA2D60322AC');
        $this->addSql('DROP TABLE company_main_user_role');
        $this->addSql('DROP TABLE role');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company_main_user_role (company_main_user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_23964EA2ADE8B0B2 (company_main_user_id), INDEX IDX_23964EA2D60322AC (role_id), PRIMARY KEY(company_main_user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE company_main_user_role ADD CONSTRAINT FK_23964EA2D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_main_user_role ADD CONSTRAINT FK_23964EA2ADE8B0B2 FOREIGN KEY (company_main_user_id) REFERENCES company_main_user (id) ON DELETE CASCADE');
    }
}
