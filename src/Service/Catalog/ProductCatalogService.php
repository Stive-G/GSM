<?php

namespace App\Service\Catalog;

use App\Infrastructure\Mongo\MongoCatalogClient;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

final class ProductCatalogService
{
    public function __construct(
        private readonly MongoCatalogClient $mongo
    ) {}

    private function products(): Collection
    {
        return $this->mongo->products();
    }

    // =========================================================
    // FIND
    // =========================================================

    public function findById(string $id): ?array
    {
        try {
            $doc = $this->products()->findOne([
                '_id' => new ObjectId($id),
            ]);
        } catch (\Throwable) {
            return null;
        }

        return $doc ? $doc->getArrayCopy() : null;
    }

    public function findBySku(string $sku): ?array
    {
        $doc = $this->products()->findOne(['sku' => $sku]);
        return $doc ? $doc->getArrayCopy() : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $doc = $this->products()->findOne(['slug' => $slug]);
        return $doc ? $doc->getArrayCopy() : null;
    }

    // =========================================================
    // SEARCH (ADMIN + FRONT)
    // =========================================================
    /**
     * @return array{
     *   items: array,
     *   total: int,
     *   page: int,
     *   limit: int
     * }
     */
    public function search(array $filters = [], int $page = 1, int $limit = 50): array
    {
        $query = [];

        // filtre texte (q)
        if (!empty($filters['text'])) {
            $query['$text'] = [
                '$search' => (string) $filters['text'],
            ];
        }

        // catégorie
        if (!empty($filters['categoryId'])) {
            $query['categoryId'] = (string) $filters['categoryId'];
        }

        $options = [
            'skip'  => max(0, ($page - 1) * $limit),
            'limit' => $limit,
            'sort'  => ['label' => 1],
        ];

        $total = $this->products()->countDocuments($query);

        $cursor = $this->products()->find($query, $options);

        $items = array_map(function ($doc) {
            $arr = $doc->getArrayCopy();
            $arr['id'] = (string) ($arr['_id'] ?? '');
            return $arr;
        }, iterator_to_array($cursor, false));

        return [
            'items' => $items,
            'total' => (int) $total,
            'page'  => $page,
            'limit' => $limit,
        ];
    }

    // =========================================================
    // CRUD
    // =========================================================

    public function create(array $data): string
    {
        $result = $this->products()->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public function update(string $id, array $data): void
    {
        unset($data['_id']);

        $this->products()->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );
    }

    public function delete(string $id): void
    {
        $this->products()->deleteOne([
            '_id' => new ObjectId($id),
        ]);
    }
}
