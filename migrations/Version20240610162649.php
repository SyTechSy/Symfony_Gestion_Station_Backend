<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240610162649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devis_station (id INT AUTO_INCREMENT NOT NULL, devis_station_id INT DEFAULT NULL, valeur_arriver DOUBLE PRECISION NOT NULL, valeur_de_depart DOUBLE PRECISION NOT NULL, consommation DOUBLE PRECISION NOT NULL, prix_unite DOUBLE PRECISION NOT NULL, budget_obtenu DOUBLE PRECISION NOT NULL, date_add_devis DATETIME NOT NULL, INDEX IDX_DD763ED911CF9DD (devis_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devis_station ADD CONSTRAINT FK_DD763ED911CF9DD FOREIGN KEY (devis_station_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis_station DROP FOREIGN KEY FK_DD763ED911CF9DD');
        $this->addSql('DROP TABLE devis_station');
    }
}
