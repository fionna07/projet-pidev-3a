<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215154336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activites DROP FOREIGN KEY FK_766B5EB5A76ED395');
        $this->addSql('DROP INDEX IDX_766B5EB5A76ED395 ON activites');
        $this->addSql('ALTER TABLE activites ADD utilisateur_id INT NOT NULL, DROP user_id, CHANGE meta_data meta_data JSON NOT NULL');
        $this->addSql('ALTER TABLE activites ADD CONSTRAINT FK_766B5EB5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_766B5EB5FB88E14F ON activites (utilisateur_id)');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3A9D1C132 ON utilisateur');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3C808BA5A ON utilisateur');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3E0B0AAA1 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur ADD reactivation_date DATETIME DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE num_tel num_tel INT NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activites DROP FOREIGN KEY FK_766B5EB5FB88E14F');
        $this->addSql('DROP INDEX IDX_766B5EB5FB88E14F ON activites');
        $this->addSql('ALTER TABLE activites ADD user_id INT DEFAULT NULL, DROP utilisateur_id, CHANGE meta_data meta_data LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE activites ADD CONSTRAINT FK_766B5EB5A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_766B5EB5A76ED395 ON activites (user_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE utilisateur DROP reactivation_date, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE num_tel num_tel VARCHAR(15) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3A9D1C132 ON utilisateur (first_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3C808BA5A ON utilisateur (last_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E0B0AAA1 ON utilisateur (num_tel)');
    }
}
