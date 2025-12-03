<?php

namespace App\Repository\Catalog;

use MongoDB\Collection;
use MongoDB\Database;

class CatalogMagasinRepository extends AbstractMongoCatalogRepository
{
    private Collection $collection;

    public function __construct(Database $database)
    {
        parent::__construct($database);
        $this->collection = $this->database->selectCollection('magasins');
        $this->collection->createIndex(['code' => 1], ['unique' => true]);
    }

    protected function getCollection(): Collection
    {
        return $this->collection;
    }

    public function upsert(array $magasin): void
    {
        $this->collection()->replaceOne(['_id' => $magasin['_id']], $magasin, ['upsert' => true]);
    }

    public function findActive(): array
    {
        return $this->collection()->find(['isActive' => true], ['sort' => ['name' => 1]])->toArray();
    }
}
