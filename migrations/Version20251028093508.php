<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251028093508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, conditionnement_id INT NOT NULL, source_id INT NOT NULL, destination_id INT NOT NULL, out_movement_id INT NOT NULL, in_movement_id INT NOT NULL, quantity NUMERIC(18, 4) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment LONGTEXT DEFAULT NULL, INDEX IDX_4034A3C07294869C (article_id), INDEX IDX_4034A3C0A222637 (conditionnement_id), INDEX IDX_4034A3C0953C1C61 (source_id), INDEX IDX_4034A3C0816C6140 (destination_id), UNIQUE INDEX UNIQ_4034A3C0D2124A95 (out_movement_id), UNIQUE INDEX UNIQ_4034A3C0D3DB76 (in_movement_id), INDEX idx_transfer_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C07294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0A222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0953C1C61 FOREIGN KEY (source_id) REFERENCES magasin (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0816C6140 FOREIGN KEY (destination_id) REFERENCES magasin (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0D2124A95 FOREIGN KEY (out_movement_id) REFERENCES mouvement_stock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0D3DB76 FOREIGN KEY (in_movement_id) REFERENCES mouvement_stock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock CHANGE quantity quantity NUMERIC(18, 4) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C07294869C');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0A222637');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0953C1C61');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0816C6140');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0D2124A95');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0D3DB76');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('ALTER TABLE stock CHANGE quantity quantity NUMERIC(18, 4) DEFAULT \'0.0000\' NOT NULL');
    }
}
