<?php

namespace App\Repository\Catalog;

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

    public function findAllActive(): array
    {
        return $this->collection()->find(['isActive' => true], ['sort' => ['position' => 1]])->toArray();
    }

    public function findTree(): array
    {
        $categories = $this->findAllActive();
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

    public function upsert(array $category): void
    {
        $this->collection()->replaceOne(['_id' => $category['_id']], $category, ['upsert' => true]);
    }
}
