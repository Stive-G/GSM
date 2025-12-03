<?php

namespace App\Repository\Catalog;

use App\Dto\Catalog\StockDto;
use MongoDB\BSON\ObjectId;
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

    public function upsert(StockDto $stock): void
    {
        $doc = $stock->toArray();
        $doc['productId'] = new ObjectId($stock->productId);
        $doc['variantId'] = $stock->variantId ? new ObjectId($stock->variantId) : null;
        $doc['magasinId'] = new ObjectId($stock->magasinId);

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
        return array_map(fn (array $doc) => StockDto::fromArray($doc), $this->collection()->find([])->toArray());
    }

    /** @return StockDto[] */
    public function findByProduct(string $productId): array
    {
        return array_map(fn (array $doc) => StockDto::fromArray($doc), $this->collection()->find(['productId' => new ObjectId($productId)])->toArray());
    }

    /** @return StockDto[] */
    public function findByProductAndVariant(string $productId, ?string $variantId): array
    {
        $filter = ['productId' => new ObjectId($productId)];
        if ($variantId !== null) {
            $filter['variantId'] = new ObjectId($variantId);
        }

        return array_map(fn (array $doc) => StockDto::fromArray($doc), $this->collection()->find($filter)->toArray());
    }

    public function delete(string $productId, ?string $variantId, string $magasinId): void
    {
        $filter = [
            'productId' => new ObjectId($productId),
            'magasinId' => new ObjectId($magasinId),
        ];
        if ($variantId !== null) {
            $filter['variantId'] = new ObjectId($variantId);
        }

        $this->collection()->deleteOne($filter);
    }
}
