<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250112202045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, conducteur_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, note INT NOT NULL, commentaire LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8F91ABF0F16F4AC6 (conducteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE avisvalidation (id INT AUTO_INCREMENT NOT NULL, conducteur_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, commentaire LONGTEXT DEFAULT NULL, note INT DEFAULT NULL, INDEX IDX_FD65E5B2F16F4AC6 (conducteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, name VARCHAR(255) NOT NULL, mail VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, trajets_id INT NOT NULL, user_id INT NOT NULL, trajetsencours_id INT DEFAULT NULL, trajetfini_id INT DEFAULT NULL, INDEX IDX_42C84955451BDEFF (trajets_id), INDEX IDX_42C84955A76ED395 (user_id), INDEX IDX_42C84955C868902D (trajetsencours_id), INDEX IDX_42C84955EA8883F1 (trajetfini_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trajets (id INT AUTO_INCREMENT NOT NULL, conducteur_id INT DEFAULT NULL, voiture_id INT DEFAULT NULL, depart VARCHAR(255) NOT NULL, arrive VARCHAR(255) NOT NULL, date DATETIME NOT NULL, duree TIME NOT NULL, prix INT NOT NULL, INDEX IDX_FF2B5BA9F16F4AC6 (conducteur_id), INDEX IDX_FF2B5BA9181A8BA (voiture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trajetsencours (id INT AUTO_INCREMENT NOT NULL, conducteur_id INT DEFAULT NULL, voiture_id INT DEFAULT NULL, depart VARCHAR(255) NOT NULL, arrive VARCHAR(255) NOT NULL, date DATETIME NOT NULL, duree INT NOT NULL, prix INT NOT NULL, INDEX IDX_919ADFFAF16F4AC6 (conducteur_id), INDEX IDX_919ADFFA181A8BA (voiture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trajetsfini (id INT AUTO_INCREMENT NOT NULL, conducteur_id INT NOT NULL, voiture_id INT NOT NULL, depart VARCHAR(255) NOT NULL, arrive VARCHAR(255) NOT NULL, date DATETIME NOT NULL, duree INT NOT NULL, prix INT NOT NULL, INDEX IDX_BCE79D83F16F4AC6 (conducteur_id), INDEX IDX_BCE79D83181A8BA (voiture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, credits INT DEFAULT NULL, api_token VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voiture (id INT AUTO_INCREMENT NOT NULL, proprietaire_id INT DEFAULT NULL, voiture VARCHAR(255) NOT NULL, dateimat DATE NOT NULL, fumeur TINYINT(1) NOT NULL, annimaux TINYINT(1) NOT NULL, marque VARCHAR(255) NOT NULL, place INT NOT NULL, modele VARCHAR(255) NOT NULL, couleur VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_E9E2810F76C50E4A (proprietaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0F16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES trajetsfini (id)');
        $this->addSql('ALTER TABLE avisvalidation ADD CONSTRAINT FK_FD65E5B2F16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES trajetsfini (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955451BDEFF FOREIGN KEY (trajets_id) REFERENCES trajets (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955C868902D FOREIGN KEY (trajetsencours_id) REFERENCES trajetsencours (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955EA8883F1 FOREIGN KEY (trajetfini_id) REFERENCES trajetsfini (id)');
        $this->addSql('ALTER TABLE trajets ADD CONSTRAINT FK_FF2B5BA9F16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trajets ADD CONSTRAINT FK_FF2B5BA9181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('ALTER TABLE trajetsencours ADD CONSTRAINT FK_919ADFFAF16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trajetsencours ADD CONSTRAINT FK_919ADFFA181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('ALTER TABLE trajetsfini ADD CONSTRAINT FK_BCE79D83F16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trajetsfini ADD CONSTRAINT FK_BCE79D83181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0F16F4AC6');
        $this->addSql('ALTER TABLE avisvalidation DROP FOREIGN KEY FK_FD65E5B2F16F4AC6');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955451BDEFF');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955C868902D');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955EA8883F1');
        $this->addSql('ALTER TABLE trajets DROP FOREIGN KEY FK_FF2B5BA9F16F4AC6');
        $this->addSql('ALTER TABLE trajets DROP FOREIGN KEY FK_FF2B5BA9181A8BA');
        $this->addSql('ALTER TABLE trajetsencours DROP FOREIGN KEY FK_919ADFFAF16F4AC6');
        $this->addSql('ALTER TABLE trajetsencours DROP FOREIGN KEY FK_919ADFFA181A8BA');
        $this->addSql('ALTER TABLE trajetsfini DROP FOREIGN KEY FK_BCE79D83F16F4AC6');
        $this->addSql('ALTER TABLE trajetsfini DROP FOREIGN KEY FK_BCE79D83181A8BA');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810F76C50E4A');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE avisvalidation');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE trajets');
        $this->addSql('DROP TABLE trajetsencours');
        $this->addSql('DROP TABLE trajetsfini');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE voiture');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
