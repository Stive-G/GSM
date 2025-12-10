<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208203810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EB7294869C');
        $this->addSql('ALTER TABLE document_ligne DROP FOREIGN KEY FK_3E51ADF17294869C');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C07294869C');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EBA222637');
        $this->addSql('ALTER TABLE document_ligne DROP FOREIGN KEY FK_3E51ADF1A222637');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0A222637');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66BCF5E72D');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B36566020096AE3');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656607294869C');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660A222637');
        $this->addSql('ALTER TABLE conditionnement DROP FOREIGN KEY FK_3F4BEA3A7294869C');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE conditionnement');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7619EB6921');
        $this->addSql('ALTER TABLE document ADD numero VARCHAR(50) NOT NULL, CHANGE client_id client_id INT DEFAULT NULL, CHANGE type type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7619EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE document_ligne DROP FOREIGN KEY FK_3E51ADF1C33F7837');
        $this->addSql('DROP INDEX IDX_3E51ADF17294869C ON document_ligne');
        $this->addSql('DROP INDEX IDX_3E51ADF1A222637 ON document_ligne');
        $this->addSql('ALTER TABLE document_ligne ADD product_id_mongo VARCHAR(50) NOT NULL, ADD unit VARCHAR(50) NOT NULL, ADD unit_price_ht NUMERIC(10, 2) NOT NULL, DROP article_id, DROP conditionnement_id, DROP unit_price, CHANGE quantity quantity NUMERIC(10, 2) NOT NULL, CHANGE designation product_label VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE document_ligne ADD CONSTRAINT FK_3E51ADF1C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('DROP INDEX IDX_61E2C8EB7294869C ON mouvement_stock');
        $this->addSql('DROP INDEX IDX_61E2C8EBA222637 ON mouvement_stock');
        $this->addSql('ALTER TABLE mouvement_stock ADD product_id_mongo VARCHAR(50) NOT NULL, ADD product_label VARCHAR(255) NOT NULL, ADD unit VARCHAR(50) NOT NULL, DROP article_id, DROP conditionnement_id');
        $this->addSql('DROP INDEX IDX_4034A3C07294869C ON transfer');
        $this->addSql('DROP INDEX IDX_4034A3C0A222637 ON transfer');
        $this->addSql('ALTER TABLE transfer ADD product_id_mongo VARCHAR(50) NOT NULL, ADD product_label VARCHAR(255) NOT NULL, ADD unit VARCHAR(50) NOT NULL, DROP article_id, DROP conditionnement_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, reference VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, price NUMERIC(12, 2) NOT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_23A0E66AEA34913 (reference), INDEX IDX_23A0E66BCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, magasin_id INT NOT NULL, conditionnement_id INT NOT NULL, quantity NUMERIC(18, 4) DEFAULT \'0.0000\' NOT NULL, INDEX IDX_4B365660A222637 (conditionnement_id), UNIQUE INDEX uniq_stock_triplet (article_id, conditionnement_id, magasin_id), INDEX idx_stock_article (article_id), INDEX idx_stock_magasin (magasin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE conditionnement (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, unit VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, default_unit_price NUMERIC(12, 2) NOT NULL, UNIQUE INDEX uniq_article_label (article_id, label), INDEX IDX_3F4BEA3A7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_497DD6345E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B36566020096AE3 FOREIGN KEY (magasin_id) REFERENCES magasin (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656607294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660A222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conditionnement ADD CONSTRAINT FK_3F4BEA3A7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mouvement_stock ADD article_id INT NOT NULL, ADD conditionnement_id INT NOT NULL, DROP product_id_mongo, DROP product_label, DROP unit');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EBA222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_61E2C8EB7294869C ON mouvement_stock (article_id)');
        $this->addSql('CREATE INDEX IDX_61E2C8EBA222637 ON mouvement_stock (conditionnement_id)');
        $this->addSql('ALTER TABLE document_ligne DROP FOREIGN KEY FK_3E51ADF1C33F7837');
        $this->addSql('ALTER TABLE document_ligne ADD article_id INT NOT NULL, ADD conditionnement_id INT NOT NULL, ADD unit_price NUMERIC(12, 2) NOT NULL, DROP product_id_mongo, DROP unit, DROP unit_price_ht, CHANGE quantity quantity NUMERIC(18, 4) NOT NULL, CHANGE product_label designation VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE document_ligne ADD CONSTRAINT FK_3E51ADF17294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE document_ligne ADD CONSTRAINT FK_3E51ADF1A222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE document_ligne ADD CONSTRAINT FK_3E51ADF1C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3E51ADF17294869C ON document_ligne (article_id)');
        $this->addSql('CREATE INDEX IDX_3E51ADF1A222637 ON document_ligne (conditionnement_id)');
        $this->addSql('ALTER TABLE transfer ADD article_id INT NOT NULL, ADD conditionnement_id INT NOT NULL, DROP product_id_mongo, DROP product_label, DROP unit');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C07294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0A222637 FOREIGN KEY (conditionnement_id) REFERENCES conditionnement (id) ON UPDATE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4034A3C07294869C ON transfer (article_id)');
        $this->addSql('CREATE INDEX IDX_4034A3C0A222637 ON transfer (conditionnement_id)');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7619EB6921');
        $this->addSql('ALTER TABLE document DROP numero, CHANGE client_id client_id INT NOT NULL, CHANGE type type VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7619EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON UPDATE NO ACTION');
    }
}
