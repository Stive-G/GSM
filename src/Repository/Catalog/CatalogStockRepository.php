<?php

namespace App\Repository\Catalog;

use App\Dto\Catalog\StockDto;
use MongoDB\Collection;
use MongoDB\Database;

class CatalogStockRepository extends AbstractMongoCatalogRepository
{
    private Collection $collection;

    public function __construct(Database $database)
    {
        parent::__construct($database);
        $this->collection = $this->database->selectCollection('stocks');

        // Index composite : (productId, variantId, magasinId) uniques
        $this->collection->createIndex(
            ['productId' => 1, 'variantId' => 1, 'magasinId' => 1],
            ['unique' => true]
        );
    }

    protected function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * Upsert d’une ligne de stock
     */
    public function upsert(StockDto $stock): void
    {
        $doc = $stock->toArray();

        // On force les types de base pour rester cohérent partout :
        // - productId : string (id du produit Mongo)
        // - variantId : string|null (ou "default")
        // - magasinId : int (id SQL du magasin)
        $doc['productId'] = (string) $stock->productId;
        $doc['variantId'] = $stock->variantId !== null ? (string) $stock->variantId : null;
        $doc['magasinId'] = (int) $stock->magasinId;

        $this->collection()->replaceOne(
            [
                'productId' => $doc['productId'],
                'variantId' => $doc['variantId'],
                'magasinId' => $doc['magasinId'],
            ],
            $doc,
            ['upsert' => true]
        );
    }

    /** @return StockDto[] */
    public function findAll(): array
    {
        return array_map(
            fn(array $doc) => StockDto::fromArray($doc),
            $this->collection()->find([])->toArray()
        );
    }

    /** @return StockDto[] */
    public function findByProduct(string $productId): array
    {
        return array_map(
            fn(array $doc) => StockDto::fromArray($doc),
            $this->collection()->find(['productId' => (string) $productId])->toArray()
        );
    }

    /** @return StockDto[] */
    public function findByProductAndVariant(string $productId, ?string $variantId): array
    {
        $filter = ['productId' => (string) $productId];
        if ($variantId !== null) {
            $filter['variantId'] = (string) $variantId;
        }

        return array_map(
            fn(array $doc) => StockDto::fromArray($doc),
            $this->collection()->find($filter)->toArray()
        );
    }

    public function delete(string $productId, ?string $variantId, string|int $magasinId): void
    {
        $filter = [
            'productId' => (string) $productId,
            'magasinId' => (int) $magasinId,
        ];
        if ($variantId !== null) {
            $filter['variantId'] = (string) $variantId;
        }

        $this->collection()->deleteOne($filter);
    }
}
