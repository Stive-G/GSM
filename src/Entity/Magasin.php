<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\MagasinRepository::class)]

// LEGACY SQL entity for catalogue (catalogue Mongo est désormais la source de vérité).
class Magasin
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length:50, unique:true)] private string $code;
    #[ORM\Column(length:150)] private string $name;

    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $c): self { $this->code = $c; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $n): self { $this->name = $n; return $this; }
}
