<?php

namespace App\Entity;

use App\Repository\DocumentLigneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentLigneRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DocumentLigne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Document $document = null;

    #[ORM\Column(name: 'product_id_mongo', length: 50)]
    private string $productIdMongo;

    #[ORM\Column(name: 'product_label', length: 255)]
    private string $productLabel;

    #[ORM\Column(length: 50)]
    private string $unit;

    #[ORM\Column(name: 'unit_price_ht', type: 'decimal', precision: 10, scale: 2)]
    private string $unitPriceHt;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $quantity = "1.00";

    // UI uniquement (pas en DB)
    private ?ProductRef $productRef = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }
    public function setDocument(?Document $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function getProductIdMongo(): string
    {
        return $this->productIdMongo;
    }
    public function setProductIdMongo(string $productIdMongo): self
    {
        $this->productIdMongo = $productIdMongo;
        return $this;
    }

    public function getProductLabel(): string
    {
        return $this->productLabel;
    }
    public function setProductLabel(string $productLabel): self
    {
        $this->productLabel = $productLabel;
        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }
    public function setUnit(string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    public function getUnitPriceHt(): string
    {
        return $this->unitPriceHt;
    }
    public function setUnitPriceHt(string $unitPriceHt): self
    {
        $this->unitPriceHt = $unitPriceHt;
        return $this;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }
    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    // UI only
    public function getProductRef(): ?ProductRef
    {
        return $this->productRef;
    }
    public function setProductRef(?ProductRef $productRef): self
    {
        $this->productRef = $productRef;
        return $this;
    }

    public function hydrateFromProductRef(): void
    {
        if (!$this->productRef) return;

        $this->productIdMongo = $this->productRef->getMongoId();
        $this->productLabel   = $this->productRef->getLabel();
        $this->unit           = $this->productRef->getUnit();
        $this->unitPriceHt    = $this->productRef->getPriceHt();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function onSave(): void
    {
        // garantit que product_id_mongo ne sera jamais null
        $this->hydrateFromProductRef();
    }
}
