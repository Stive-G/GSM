<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105201642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0816C6140');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0953C1C61');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0D2124A95');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0D3DB76');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EB20096AE3');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('DROP TABLE mouvement_stock');
        $this->addSql('DROP TABLE magasin');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, source_id INT NOT NULL, destination_id INT NOT NULL, out_movement_id INT NOT NULL, in_movement_id INT NOT NULL, quantity NUMERIC(18, 4) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, product_id_mongo VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, product_label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, unit VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX idx_transfer_created_at (created_at), INDEX IDX_4034A3C0816C6140 (destination_id), INDEX IDX_4034A3C0953C1C61 (source_id), UNIQUE INDEX UNIQ_4034A3C0D3DB76 (in_movement_id), UNIQUE INDEX UNIQ_4034A3C0D2124A95 (out_movement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE mouvement_stock (id INT AUTO_INCREMENT NOT NULL, magasin_id INT NOT NULL, type VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, quantity NUMERIC(18, 4) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, product_id_mongo VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, product_label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, unit VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX idx_mvt_created_at (created_at), INDEX idx_mvt_type (type), INDEX IDX_61E2C8EB20096AE3 (magasin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE magasin (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_54AF5F2777153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0816C6140 FOREIGN KEY (destination_id) REFERENCES magasin (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0953C1C61 FOREIGN KEY (source_id) REFERENCES magasin (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0D2124A95 FOREIGN KEY (out_movement_id) REFERENCES mouvement_stock (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0D3DB76 FOREIGN KEY (in_movement_id) REFERENCES mouvement_stock (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB20096AE3 FOREIGN KEY (magasin_id) REFERENCES magasin (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
