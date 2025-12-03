<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\StockRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_stock_triplet',
    columns: ['article_id','conditionnement_id','magasin_id']
)]
#[ORM\Index(columns: ['article_id'], name: 'idx_stock_article')]
#[ORM\Index(columns: ['magasin_id'], name: 'idx_stock_magasin')]
// LEGACY SQL entity for catalogue (catalogue Mongo est désormais la source de vérité).
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column] private ?int $id = null;

    // Redondant mais pratique pour requêtes
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Article $article;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Conditionnement $conditionnement;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Magasin $magasin;

    #[ORM\Column(type:'decimal', precision: 18, scale: 4, options: ['default'=>0])]
    private string $quantity = '0.0000';

    public function getId(): ?int { return $this->id; }

    public function getArticle(): Article { return $this->article; }
    public function setArticle(Article $a): self { $this->article = $a; return $this; }

    public function getConditionnement(): Conditionnement { return $this->conditionnement; }
    public function setConditionnement(Conditionnement $c): self { $this->conditionnement = $c; return $this; }

    public function getMagasin(): Magasin { return $this->magasin; }
    public function setMagasin(Magasin $m): self { $this->magasin = $m; return $this; }

    public function getQuantity(): string { return $this->quantity; }
    public function setQuantity(string $q): self { $this->quantity = $q; return $this; }
}
