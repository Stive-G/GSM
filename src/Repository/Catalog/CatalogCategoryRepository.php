<?php

namespace App\Repository\Catalog;

use App\Dto\Catalog\CategoryDto;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Database;

class CatalogCategoryRepository extends AbstractMongoCatalogRepository
{
    private Collection $collection;

    public function __construct(Database $database)
    {
        parent::__construct($database);
        $this->collection = $this->database->selectCollection('categories');
        $this->collection->createIndex(['slug' => 1], ['unique' => true]);
        $this->collection->createIndex(['parentId' => 1]);
        $this->collection->createIndex(['isActive' => 1]);
    }

    protected function getCollection(): Collection
    {
        return $this->collection;
    }

    /** @return CategoryDto[] */
    public function findAll(): array
    {
        return array_map(fn (array $doc) => CategoryDto::fromArray($doc), $this->collection()->find([], ['sort' => ['position' => 1, 'name' => 1]])->toArray());
    }

    /** @return CategoryDto[] */
    public function findAllActive(): array
    {
        return array_map(fn (array $doc) => CategoryDto::fromArray($doc), $this->collection()->find(['isActive' => true], ['sort' => ['position' => 1]])->toArray());
    }

    public function findTree(): array
    {
        $categories = array_map(fn (CategoryDto $dto) => $dto->toArray(), $this->findAllActive());
        $byId = [];
        foreach ($categories as $category) {
            $category['children'] = [];
            $byId[$category['_id']] = $category;
        }

        $tree = [];
        foreach ($byId as $id => &$category) {
            $parentId = $category['parentId'] ?? null;
            if ($parentId && isset($byId[$parentId])) {
                $byId[$parentId]['children'][] = &$category;
            } else {
                $tree[] = &$category;
            }
        }

        return $tree;
    }

    public function findBySlug(string $slug): ?array
    {
        $result = $this->collection()->findOne(['slug' => $slug, 'isActive' => true]);
        return $result ? (array) $result : null;
    }

    public function find(string $id): ?CategoryDto
    {
        $doc = $this->collection()->findOne(['_id' => new ObjectId($id)]);
        return $doc ? CategoryDto::fromArray((array) $doc) : null;
    }

    public function create(CategoryDto $category): CategoryDto
    {
        $id = new ObjectId();
        $category->id = (string) $id;
        $this->collection()->insertOne($this->prepareDocument($category));

        return $category;
    }

    public function update(CategoryDto $category): void
    {
        if (!$category->id) {
            throw new \InvalidArgumentException('Category must have an id for update');
        }

        $this->collection()->updateOne(['_id' => new ObjectId($category->id)], ['\$set' => $this->prepareDocument($category)]);
    }

    public function delete(string $id): void
    {
        $this->collection()->deleteOne(['_id' => new ObjectId($id)]);
    }

    /** @deprecated kept for legacy sync usage */
    public function upsert(array $category): void
    {
        $this->collection()->replaceOne(['_id' => $category['_id']], $category, ['upsert' => true]);
    }

    private function prepareDocument(CategoryDto $category): array
    {
        return [
            '_id' => new ObjectId($category->id),
            'name' => $category->name,
            'slug' => $category->slug,
            'parentId' => $category->parentId ? new ObjectId($category->parentId) : null,
            'position' => $category->position,
            'isActive' => $category->isActive,
        ];
    }
}
