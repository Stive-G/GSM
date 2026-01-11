<?php

namespace App\Service\Catalog;

use App\Infrastructure\Mongo\MongoCatalogClient;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

final class CategoryCatalogService
{
    public function __construct(private readonly MongoCatalogClient $mongo) {}

    private function categories(): Collection
    {
        return $this->mongo->categories();
    }

    public function findAll(): array
    {
        $cursor = $this->categories()->find([], ['sort' => ['name' => 1]]);
        return array_map(fn($d) => $d->getArrayCopy(), iterator_to_array($cursor, false));
    }

    public function findById(string $id): ?array
    {
        try {
            $doc = $this->categories()->findOne(['_id' => new ObjectId($id)]);
            return $doc ? $doc->getArrayCopy() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function create(array $data): string
    {
        $res = $this->categories()->insertOne($data);
        return (string) $res->getInsertedId();
    }

    public function update(string $id, array $data): void
    {
        unset($data['_id']);
        $this->categories()->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );
    }

    public function delete(string $id): void
    {
        $this->categories()->deleteOne(['_id' => new ObjectId($id)]);
    }
}
