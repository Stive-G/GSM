<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\DocumentLigneRepository::class)]
class DocumentLigne
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Document $document = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Article $article = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Conditionnement $conditionnement = null;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 4)]
    #[Assert\Positive(message: 'Quantité > 0')]
    private string $quantity = '1.0000';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $unitPrice = '0.00';

    #[ORM\Column(length: 255)]
    private string $designation;

    public function getId(): ?int { return $this->id; }
    public function getDocument(): ?Document { return $this->document; }
    public function setDocument(?Document $d): self { $this->document = $d; return $this; }
    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $a): self { $this->article = $a; return $this; }
    public function getConditionnement(): ?Conditionnement { return $this->conditionnement; }
    public function setConditionnement(?Conditionnement $c): self { $this->conditionnement = $c; return $this; }
    public function getQuantity(): string { return $this->quantity; }
    public function setQuantity(string $q): self { $this->quantity = $q; return $this; }
    public function getUnitPrice(): string { return $this->unitPrice; }
    public function setUnitPrice(string $p): self { $this->unitPrice = $p; return $this; }
    public function getDesignation(): string { return $this->designation; }
    public function setDesignation(string $d): self { $this->designation = $d; return $this; }
}
