<?php

namespace App\Service\Catalog;

use App\Infrastructure\Mongo\MongoCatalogClient;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

final class ProductCatalogService
{
    public function __construct(
        private MongoCatalogClient $mongo
    ) {}

    private function products(): Collection
    {
        return $this->mongo->products();
    }

    public function findById(string $id): ?array
    {
        $doc = $this->products()->findOne([
            '_id' => new ObjectId($id),
        ]);

        return $doc ? $doc->getArrayCopy() : null;
    }

    public function search(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $query = [];
        $options = [
            'skip'  => ($page - 1) * $limit,
            'limit' => $limit,
            'sort'  => ['label' => 1],
        ];

        if (!empty($filters['categoryId'])) {
            $query['categoryId'] = $filters['categoryId'];
        }

        if (!empty($filters['text'])) {
            $query['$text'] = ['$search' => $filters['text']];
        }

        $cursor = $this->products()->find($query, $options);

        return iterator_to_array($cursor, false);
    }

    public function create(array $data): string
    {
        $result = $this->products()->insertOne($data);

        return (string) $result->getInsertedId();
    }

    public function update(string $id, array $data): void
    {
        unset($data['_id']); // sécurité

        $this->products()->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );
    }

    public function delete(string $id): void
    {
        $this->products()->deleteOne(['_id' => new ObjectId($id)]);
    }
}
