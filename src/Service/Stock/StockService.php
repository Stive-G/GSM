<?php

namespace App\Service\Stock;

use App\Infrastructure\Mongo\MongoCatalogClient;
use MongoDB\Collection;

final class StockService
{
    public function __construct(private readonly MongoCatalogClient $mongo) {}

    private function stocks(): Collection
    {
        return $this->mongo->stocks();
    }

    public function findByProductId(string $productId): array
    {
        $cursor = $this->stocks()->find(['productId' => $productId], ['sort' => ['magasinId' => 1]]);
        return array_map(fn($d) => $d->getArrayCopy(), iterator_to_array($cursor, false));
    }

    public function getStock(string $productId, ?string $variantId, int $magasinId): int
    {
        $doc = $this->stocks()->findOne([
            'productId' => $productId,
            'variantId' => $variantId,
            'magasinId' => $magasinId,
        ]);

        return $doc ? (int) ($doc['quantity'] ?? 0) : 0;
    }

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
