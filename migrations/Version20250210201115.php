<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210201115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achat (id INT AUTO_INCREMENT NOT NULL, equipement_id INT DEFAULT NULL, date DATETIME NOT NULL, total INT NOT NULL, UNIQUE INDEX UNIQ_26A98456806F0F5C (equipement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE activites (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, action VARCHAR(255) NOT NULL, meta_data JSON NOT NULL, date DATETIME NOT NULL, INDEX IDX_766B5EB5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, offre_id INT DEFAULT NULL, employe_id INT DEFAULT NULL, date_candidature DATETIME NOT NULL, competences VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_E33BD3B84CC8505A (offre_id), INDEX IDX_E33BD3B81B65292 (employe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, date_commande DATE NOT NULL, quantite INT NOT NULL, status VARCHAR(255) NOT NULL, mode_paiement VARCHAR(255) NOT NULL, adresse_livraison VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipement (id INT AUTO_INCREMENT NOT NULL, fournisseur_id INT DEFAULT NULL, nom_equipement VARCHAR(255) NOT NULL, quantite_disponible INT NOT NULL, prix DOUBLE PRECISION NOT NULL, image VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, INDEX IDX_B8B4C6F3670C757F (fournisseur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, agriculteur_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_debut DATETIME NOT NULL, type_visite VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, INDEX IDX_B26681E7EBB810E (agriculteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offre_emploi (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, nombre_postes INT NOT NULL, date_debut DATETIME NOT NULL, date_fin_estimee DATETIME NOT NULL, status VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, date_publication DATETIME NOT NULL, competences_requises VARCHAR(255) NOT NULL, salaire DOUBLE PRECISION NOT NULL, INDEX IDX_132AD0D1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, agriculteur_id INT DEFAULT NULL, commande_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, quantite INT NOT NULL, prix_total DOUBLE PRECISION NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, stock_d VARCHAR(255) NOT NULL, INDEX IDX_29A5EC277EBB810E (agriculteur_id), INDEX IDX_29A5EC2782EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, evenement_id INT DEFAULT NULL, client_id INT DEFAULT NULL, date_reservation DATE NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_42C84955FD02F13 (evenement_id), INDEX IDX_42C8495519EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE terrain (id INT AUTO_INCREMENT NOT NULL, adresse VARCHAR(255) NOT NULL, surface DOUBLE PRECISION NOT NULL, prix DOUBLE PRECISION NOT NULL, status VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, typesol VARCHAR(255) DEFAULT NULL, proprietaire VARCHAR(255) NOT NULL, type_sol VARCHAR(255) NOT NULL, photos VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, terrain_id INT DEFAULT NULL, client_id INT DEFAULT NULL, agriculteur_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, date_transaction DATE NOT NULL, prix_transaction DOUBLE PRECISION NOT NULL, etat VARCHAR(255) NOT NULL, conversation LONGTEXT NOT NULL, INDEX IDX_723705D18A2D8B41 (terrain_id), INDEX IDX_723705D119EB6921 (client_id), INDEX IDX_723705D17EBB810E (agriculteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, status VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, image VARCHAR(255) NOT NULL, role LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', adresse VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE achat ADD CONSTRAINT FK_26A98456806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id)');
        $this->addSql('ALTER TABLE activites ADD CONSTRAINT FK_766B5EB5A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B84CC8505A FOREIGN KEY (offre_id) REFERENCES offre_emploi (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B81B65292 FOREIGN KEY (employe_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F3670C757F FOREIGN KEY (fournisseur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E7EBB810E FOREIGN KEY (agriculteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D1A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC277EBB810E FOREIGN KEY (agriculteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18A2D8B41 FOREIGN KEY (terrain_id) REFERENCES terrain (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D119EB6921 FOREIGN KEY (client_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D17EBB810E FOREIGN KEY (agriculteur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE achat DROP FOREIGN KEY FK_26A98456806F0F5C');
        $this->addSql('ALTER TABLE activites DROP FOREIGN KEY FK_766B5EB5A76ED395');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B84CC8505A');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B81B65292');
        $this->addSql('ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F3670C757F');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E7EBB810E');
        $this->addSql('ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D1A76ED395');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC277EBB810E');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2782EA2E54');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955FD02F13');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D18A2D8B41');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D119EB6921');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D17EBB810E');
        $this->addSql('DROP TABLE achat');
        $this->addSql('DROP TABLE activites');
        $this->addSql('DROP TABLE candidature');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE equipement');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE offre_emploi');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE terrain');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
