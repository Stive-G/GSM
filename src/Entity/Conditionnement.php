<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ConditionnementRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_article_label',
    columns: ['article_id', 'label']
)]
// LEGACY SQL entity for catalogue (catalogue Mongo est désormais la source de vérité).
class Conditionnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column] private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'conditionnements')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Article $article = null;

    #[ORM\Column(length: 100)]
    private string $label; // ex: "Pièce", "Boîte 10", "Kg", "m3"

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $unit = null; // optionnel: "pcs","kg","m","m2", etc.

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $defaultUnitPrice = '0.00';

    public function getId(): ?int { return $this->id; }
    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $a): self { $this->article = $a; return $this; }

    public function getLabel(): string { return $this->label; }
    public function setLabel(string $l): self { $this->label = $l; return $this; }

    public function getUnit(): ?string { return $this->unit; }
    public function setUnit(?string $u): self { $this->unit = $u; return $this; }

    public function getDefaultUnitPrice(): string { return $this->defaultUnitPrice; }
    public function setDefaultUnitPrice(string $p): self { $this->defaultUnitPrice = $p; return $this; }

    public function __toString(): string
    {
        $u = $this->unit ? " ({$this->unit})" : '';
        return sprintf('%s%s', $this->label, $u);
    }
}
