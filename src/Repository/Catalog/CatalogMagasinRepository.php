<?php

namespace App\Repository\Catalog;

use App\Dto\Catalog\MagasinDto;
use MongoDB\BSON\ObjectId;
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

    /** @return MagasinDto[] */
    public function findActive(): array
    {
        return array_map(fn (array $doc) => MagasinDto::fromArray($doc), $this->collection()->find(['isActive' => true], ['sort' => ['name' => 1]])->toArray());
    }

    /** @return MagasinDto[] */
    public function findAll(): array
    {
        return array_map(fn (array $doc) => MagasinDto::fromArray($doc), $this->collection()->find([], ['sort' => ['name' => 1]])->toArray());
    }

    public function find(string $id): ?MagasinDto
    {
        $doc = $this->collection()->findOne(['_id' => new ObjectId($id)]);
        return $doc ? MagasinDto::fromArray((array) $doc) : null;
    }

    public function create(MagasinDto $magasin): MagasinDto
    {
        $magasin->id = (string) new ObjectId();
        $this->collection()->insertOne($this->prepareDocument($magasin));

        return $magasin;
    }

    public function update(MagasinDto $magasin): void
    {
        if (!$magasin->id) {
            throw new \InvalidArgumentException('Magasin must have an id for update');
        }

        $this->collection()->updateOne(['_id' => new ObjectId($magasin->id)], ['\$set' => $this->prepareDocument($magasin)]);
    }

    public function delete(string $id): void
    {
        $this->collection()->deleteOne(['_id' => new ObjectId($id)]);
    }

    /** @deprecated kept for legacy sync usage */
    public function upsert(array $magasin): void
    {
        $this->collection()->replaceOne(['_id' => $magasin['_id']], $magasin, ['upsert' => true]);
    }

    private function prepareDocument(MagasinDto $magasin): array
    {
        return [
            '_id' => new ObjectId($magasin->id),
            'name' => $magasin->name,
            'code' => $magasin->code,
            'isActive' => $magasin->isActive,
        ];
    }
}
