<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\TransferRepository::class)]
#[ORM\Table(name: 'transfer')]
#[ORM\Index(columns: ['created_at'], name: 'idx_transfer_created_at')]
class Transfer
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Article $article = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Conditionnement $conditionnement = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Magasin $source = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Magasin $destination = null;

    #[ORM\Column(type:'decimal', precision: 18, scale: 4)]
    private string $quantity = '0.0000';

    #[ORM\Column(type:'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type:'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MouvementStock $outMovement = null;

    #[ORM\OneToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MouvementStock $inMovement = null;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $a): self { $this->article = $a; return $this; }

    public function getConditionnement(): ?Conditionnement { return $this->conditionnement; }
    public function setConditionnement(?Conditionnement $c): self { $this->conditionnement = $c; return $this; }

    public function getSource(): ?Magasin { return $this->source; }
    public function setSource(?Magasin $m): self { $this->source = $m; return $this; }

    public function getDestination(): ?Magasin { return $this->destination; }
    public function setDestination(?Magasin $m): self { $this->destination = $m; return $this; }

    public function getQuantity(): string { return $this->quantity; }
    public function setQuantity(string $q): self { $this->quantity = $q; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $c): self { $this->comment = $c; return $this; }

    public function getOutMovement(): ?MouvementStock { return $this->outMovement; }
    public function setOutMovement(?MouvementStock $m): self { $this->outMovement = $m; return $this; }

    public function getInMovement(): ?MouvementStock { return $this->inMovement; }
    public function setInMovement(?MouvementStock $m): self { $this->inMovement = $m; return $this; }

    public function __toString(): string
    {
        return sprintf('Transfert %s %s → %s (%s %s)',
            $this->article?->getReference() ?? '',
            $this->source?->getName() ?? '',
            $this->destination?->getName() ?? '',
            $this->quantity,
            $this->conditionnement?->getLabel() ?? ''
        );
    }
}
