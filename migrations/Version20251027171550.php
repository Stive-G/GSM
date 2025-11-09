<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027171550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, phone VARCHAR(40) DEFAULT NULL, email VARCHAR(190) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, type VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D8698A7619EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_ligne (id INT AUTO_INCREMENT NOT NULL, document_id INT NOT NULL, article_id INT NOT NULL, conditionnement_id INT NOT NULL, quantity NUMERIC(18, 4) NOT NULL, unit_price NUMERIC(12, 2) NOT NULL, designation VARCHAR(255) NOT NULL, INDEX IDX_3E51ADF1C33F7837 (document_id), INDEX IDX_3E51ADF17294869C (article_id), INDEX IDX_3E51ADF1A222637 (conditionnement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7619EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE document_ligne ADD CONSTRAINT FK_3E51ADF1C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_ligne ADD CONSTRAINT FK_3E51ADF17294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE document_ligne ADD CONSTRAINT FK_3E51ADF1A222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE stock CHANGE quantity quantity NUMERIC(18, 4) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7619EB6921');
        $this->addSql('ALTER TABLE document_ligne DROP FOREIGN KEY FK_3E51ADF1C33F7837');
        $this->addSql('ALTER TABLE document_ligne DROP FOREIGN KEY FK_3E51ADF17294869C');
        $this->addSql('ALTER TABLE document_ligne DROP FOREIGN KEY FK_3E51ADF1A222637');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE document_ligne');
        $this->addSql('ALTER TABLE stock CHANGE quantity quantity NUMERIC(18, 4) DEFAULT \'0.0000\' NOT NULL');
    }
}
