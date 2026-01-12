<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    public const TYPE_DEVIS = 'DEVIS';
    public const TYPE_VENTE = 'VENTE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $type = self::TYPE_DEVIS;

    #[ORM\Column(length: 50)]
    private ?string $numero = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Client $client = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(
        mappedBy: 'document',
        targetEntity: DocumentLigne::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }
    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, DocumentLigne> */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(DocumentLigne $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setDocument($this);
        }
        return $this;
    }

    public function removeLigne(DocumentLigne $ligne): self
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getDocument() === $this) {
                $ligne->setDocument(null);
            }
        }
        return $this;
    }
}
