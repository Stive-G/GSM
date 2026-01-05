<?php

namespace App\Service\Stock;

use App\Entity\MouvementStock;
use App\Infrastructure\Mongo\MongoCatalogClient;
use MongoDB\Collection;

final class StockService
{
    public function __construct(
        private MongoCatalogClient $mongo,
    ) {}

    private function stocks(): Collection
    {
        return $this->mongo->stocks();
    }

    /**
     * Retourne la quantité en stock pour un produit/variante/magasin
     */
    public function getStock(string $productId, ?string $variantId, int $magasinId): int
    {
        $doc = $this->stocks()->findOne([
            'productId' => $productId,
            'variantId' => $variantId,
            'magasinId' => $magasinId,
        ]);

        return $doc ? (int) ($doc['quantity'] ?? 0) : 0;
    }

    /**
     * Fixe la quantité (upsert)
     */
    public function setStock(string $productId, ?string $variantId, int $magasinId, int $quantity): void
    {
        $this->stocks()->updateOne(
            [
                'productId' => $productId,
                'variantId' => $variantId,
                'magasinId' => $magasinId,
            ],
            [
                '$set' => [
                    'quantity' => $quantity,
                ],
            ],
            ['upsert' => true]
        );
    }

    /**
     * Incrémente/décrémente la quantité (delta peut être négatif)
     */
    public function incrementStock(string $productId, ?string $variantId, int $magasinId, int|float $delta): void
    {
        $this->stocks()->updateOne(
            [
                'productId' => $productId,
                'variantId' => $variantId,
                'magasinId' => $magasinId,
            ],
            [
                '$inc' => [
                    'quantity' => (float) $delta,
                ],
            ],
            ['upsert' => true]
        );
    }
}
