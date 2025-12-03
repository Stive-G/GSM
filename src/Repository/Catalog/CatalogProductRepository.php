<?php

namespace App\Repository\Catalog;

use MongoDB\Collection;
use MongoDB\Database;

class CatalogProductRepository extends AbstractMongoCatalogRepository
{
    private Collection $collection;

    public function __construct(Database $database)
    {
        parent::__construct($database);
        $this->collection = $this->database->selectCollection('products');
        $this->collection->createIndex(['slug' => 1], ['unique' => true]);
        $this->collection->createIndex(['categories' => 1]);
        $this->collection->createIndex(['isActive' => 1]);
        $this->collection->createIndex(['brand' => 1]);
        $this->collection->createIndex(['variants.price.ttc' => 1]);
        $this->collection->createIndex(['name' => 'text', 'description' => 'text'], ['weights' => ['name' => 5, 'description' => 2]]);
    }

    protected function getCollection(): Collection
    {
        return $this->collection;
    }

    public function findBySlug(string $slug): ?array
    {
        $doc = $this->collection()->findOne(['slug' => $slug, 'isActive' => true]);
        return $doc ? (array) $doc : null;
    }

    public function search(array $filters = [], array $options = []): array
    {
        $query = ['isActive' => true];

        if (!empty($filters['categoryId'])) {
            $query['categories'] = $filters['categoryId'];
        }

        if (!empty($filters['brand'])) {
            $query['brand'] = $filters['brand'];
        }

        if (!empty($filters['attributes']) && is_array($filters['attributes'])) {
            foreach ($filters['attributes'] as $name => $value) {
                if (is_array($value)) {
                    $query["attributes.$name"] = ['$in' => array_values($value)];
                } else {
                    $query["attributes.$name"] = $value;
                }
            }
        }

        $priceMin = $filters['priceMin'] ?? null;
        $priceMax = $filters['priceMax'] ?? null;
        if ($priceMin !== null || $priceMax !== null) {
            $range = [];
            if ($priceMin !== null) {
                $range['$gte'] = (float) $priceMin;
            }
            if ($priceMax !== null) {
                $range['$lte'] = (float) $priceMax;
            }
            $query['variants'] = [
                '$elemMatch' => [
                    'isActive' => true,
                    'price.ttc' => $range,
                ],
            ];
        }

        if (!empty($filters['text'])) {
            $query['$text'] = ['$search' => $filters['text']];
        }

        $sort = $this->resolveSort($filters['sort'] ?? null);
        $limit = $options['limit'] ?? 50;
        $skip = $options['skip'] ?? 0;

        $cursor = $this->collection()->find($query, [
            'sort' => $sort,
            'limit' => $limit,
            'skip' => $skip,
        ]);

        return $cursor->toArray();
    }

    public function upsert(array $product): void
    {
        $this->collection()->replaceOne(['_id' => $product['_id']], $product, ['upsert' => true]);
    }

    /**
     * @return array<string,int>
     */
    private function resolveSort(?string $sort): array
    {
        return match ($sort) {
            'price_asc' => ['variants.price.ttc' => 1],
            'price_desc' => ['variants.price.ttc' => -1],
            'newest' => ['createdAt' => -1],
            'name' => ['name' => 1],
            default => ['updatedAt' => -1],
        };
    }
}
