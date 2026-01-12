<?php

namespace App\Entity;

use App\Repository\ProductRefRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRefRepository::class)]
class ProductRef
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // _id Mongo (ObjectId) stocké en string
    #[ORM\Column(length: 50, unique: true)]
    private string $mongoId;

    #[ORM\Column(length: 255)]
    private string $label;

    #[ORM\Column(length: 50)]
    private string $unit;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $priceHt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $syncedAt = null;

    public function __toString(): string
    {
        return $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMongoId(): string
    {
        return $this->mongoId;
    }
    public function setMongoId(string $mongoId): self
    {
        $this->mongoId = $mongoId;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
    public function setLabel(string $label): self
    {
        $this->label = $label;
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

    public function getPriceHt(): string
    {
        return $this->priceHt;
    }
    public function setPriceHt(string $priceHt): self
    {
        $this->priceHt = $priceHt;
        return $this;
    }

    public function getSyncedAt(): ?\DateTimeImmutable
    {
        return $this->syncedAt;
    }
    public function setSyncedAt(?\DateTimeImmutable $syncedAt): self
    {
        $this->syncedAt = $syncedAt;
        return $this;
    }
}
