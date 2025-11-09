<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027144812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conditionnement (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, label VARCHAR(100) NOT NULL, unit VARCHAR(20) DEFAULT NULL, default_unit_price NUMERIC(12, 2) NOT NULL, INDEX IDX_3F4BEA3A7294869C (article_id), UNIQUE INDEX uniq_article_label (article_id, label), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conditionnement ADD CONSTRAINT FK_3F4BEA3A7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mouvement_stock ADD conditionnement_id INT NOT NULL, CHANGE quantity quantity NUMERIC(18, 4) NOT NULL');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EBA222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_61E2C8EBA222637 ON mouvement_stock (conditionnement_id)');
        $this->addSql('ALTER TABLE mouvement_stock RENAME INDEX idx_mouvement_type TO idx_mvt_type');
        $this->addSql('ALTER TABLE mouvement_stock RENAME INDEX idx_mouvement_created_at TO idx_mvt_created_at');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B36566020096AE3');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656607294869C');
        $this->addSql('DROP INDEX UNIQ_4B3656607294869C20096AE3 ON stock');
        $this->addSql('ALTER TABLE stock ADD conditionnement_id INT NOT NULL, CHANGE quantity quantity NUMERIC(18, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660A222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B36566020096AE3 FOREIGN KEY (magasin_id) REFERENCES magasin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656607294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4B365660A222637 ON stock (conditionnement_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_stock_triplet ON stock (article_id, conditionnement_id, magasin_id)');
        $this->addSql('ALTER TABLE stock RENAME INDEX idx_4b3656607294869c TO idx_stock_article');
        $this->addSql('ALTER TABLE stock RENAME INDEX idx_4b36566020096ae3 TO idx_stock_magasin');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EBA222637');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660A222637');
        $this->addSql('ALTER TABLE conditionnement DROP FOREIGN KEY FK_3F4BEA3A7294869C');
        $this->addSql('DROP TABLE conditionnement');
        $this->addSql('DROP INDEX IDX_61E2C8EBA222637 ON mouvement_stock');
        $this->addSql('ALTER TABLE mouvement_stock DROP conditionnement_id, CHANGE quantity quantity NUMERIC(12, 3) NOT NULL');
        $this->addSql('ALTER TABLE mouvement_stock RENAME INDEX idx_mvt_type TO idx_mouvement_type');
        $this->addSql('ALTER TABLE mouvement_stock RENAME INDEX idx_mvt_created_at TO idx_mouvement_created_at');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656607294869C');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B36566020096AE3');
        $this->addSql('DROP INDEX IDX_4B365660A222637 ON stock');
        $this->addSql('DROP INDEX uniq_stock_triplet ON stock');
        $this->addSql('ALTER TABLE stock DROP conditionnement_id, CHANGE quantity quantity NUMERIC(12, 3) DEFAULT \'0.000\' NOT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656607294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B36566020096AE3 FOREIGN KEY (magasin_id) REFERENCES magasin (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B3656607294869C20096AE3 ON stock (article_id, magasin_id)');
        $this->addSql('ALTER TABLE stock RENAME INDEX idx_stock_article TO IDX_4B3656607294869C');
        $this->addSql('ALTER TABLE stock RENAME INDEX idx_stock_magasin TO IDX_4B36566020096AE3');
    }
}
