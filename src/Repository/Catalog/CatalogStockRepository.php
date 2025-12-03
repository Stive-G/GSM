<?php

namespace App\Repository\Catalog;

use MongoDB\Collection;
use MongoDB\Database;

class CatalogStockRepository extends AbstractMongoCatalogRepository
{
    private Collection $collection;

    public function __construct(Database $database)
    {
        parent::__construct($database);
        $this->collection = $this->database->selectCollection('stocks');
        $this->collection->createIndex(['productId' => 1, 'variantId' => 1, 'magasinId' => 1], ['unique' => true]);
    }

    protected function getCollection(): Collection
    {
        return $this->collection;
    }

    public function upsert(array $stock): void
    {
        $this->collection()->replaceOne([
            'productId' => $stock['productId'],
            'variantId' => $stock['variantId'] ?? null,
            'magasinId' => $stock['magasinId'],
        ], $stock, ['upsert' => true]);
    }

    public function findByProduct(string $productId): array
    {
        return $this->collection()->find(['productId' => $productId])->toArray();
    }

    public function findByProductAndVariant(string $productId, ?string $variantId): array
    {
        $filter = ['productId' => $productId];
        if ($variantId !== null) {
            $filter['variantId'] = $variantId;
        }
        return $this->collection()->find($filter)->toArray();
    }
}
