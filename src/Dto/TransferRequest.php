<?php
namespace App\Dto;

use App\Entity\Article;
use App\Entity\Conditionnement;
use App\Entity\Magasin;
use Symfony\Component\Validator\Constraints as Assert;

class TransferRequest
{
    #[Assert\NotNull] public ?Article $article = null;
    #[Assert\NotNull] public ?Conditionnement $conditionnement = null;
    #[Assert\NotNull] public ?Magasin $source = null;
    #[Assert\NotNull] public ?Magasin $destination = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?float $quantity = null;

    public ?string $comment = null;
}
