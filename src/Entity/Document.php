<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\DocumentRepository::class)]
class Document
{
    public const TYPE_DEVIS = 'DEVIS';
    public const TYPE_VENTE = 'VENTE';

    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: [self::TYPE_DEVIS, self::TYPE_VENTE])]
    private string $type = self::TYPE_DEVIS;

    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Client $client = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'document', targetEntity: DocumentLigne::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $lignes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function setType(string $t): self { $this->type = $t; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $c): self { $this->client = $c; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    /** @return Collection<int, DocumentLigne> */
    public function getLignes(): Collection { return $this->lignes; }
    public function addLigne(DocumentLigne $l): self {
        if (!$this->lignes->contains($l)) { $this->lignes->add($l); $l->setDocument($this); }
        return $this;
    }
    public function removeLigne(DocumentLigne $l): self {
        if ($this->lignes->removeElement($l) && $l->getDocument() === $this) { $l->setDocument(null); }
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s #%d', $this->type, $this->id ?? 0);
    }
}
