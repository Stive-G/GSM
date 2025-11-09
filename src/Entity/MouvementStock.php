<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\MouvementStockRepository::class)]
#[ORM\Table(name: 'mouvement_stock')]
#[ORM\Index(columns: ['type'], name: 'idx_mvt_type')]
#[ORM\Index(columns: ['created_at'], name: 'idx_mvt_created_at')]
class MouvementStock
{
    public const TYPE_IN = 'IN';
    public const TYPE_OUT = 'OUT';
    public const TYPE_ADJUST = 'ADJUST';
    public const TYPE_LOSS = 'LOSS'; // perte/casse

    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull] private ?Article $article = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull] private ?Conditionnement $conditionnement = null;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull] private ?Magasin $magasin = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_IN, self::TYPE_OUT, self::TYPE_ADJUST, self::TYPE_LOSS])]
    private string $type = self::TYPE_IN;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 4)]
    #[Assert\NotBlank] #[Assert\PositiveOrZero]
    private string $quantity = '0.0000';

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $a): self { $this->article = $a; return $this; }

    public function getConditionnement(): ?Conditionnement { return $this->conditionnement; }
    public function setConditionnement(?Conditionnement $c): self { $this->conditionnement = $c; return $this; }

    public function getMagasin(): ?Magasin { return $this->magasin; }
    public function setMagasin(?Magasin $m): self { $this->magasin = $m; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $t): self { $this->type = $t; return $this; }

    public function getQuantity(): string { return $this->quantity; }
    public function setQuantity(string $q): self { $this->quantity = $q; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $d): self { $this->createdAt = $d; return $this; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $c): self { $this->comment = $c; return $this; }

    public function isEntry(): bool { return $this->type === self::TYPE_IN; }
    public function isExit(): bool { return $this->type === self::TYPE_OUT; }
    public function isAdjust(): bool { return $this->type === self::TYPE_ADJUST; }
    public function isLoss(): bool { return $this->type === self::TYPE_LOSS; }

    public function __toString(): string
    {
        return sprintf('%s %s (%s / %s)', $this->type, $this->quantity, $this->article?->getReference(), $this->conditionnement?->getLabel());
    }
}
