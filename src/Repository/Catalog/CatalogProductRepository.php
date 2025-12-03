<?php

namespace App\Repository\Catalog;

use App\Dto\Catalog\ProductDto;
use App\Dto\Catalog\VariantDto;
use MongoDB\BSON\ObjectId;
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

    public function find(string $id): ?ProductDto
    {
        $doc = $this->collection()->findOne(['_id' => new ObjectId($id)]);
        return $doc ? ProductDto::fromArray((array) $doc) : null;
    }

    /** @return ProductDto[] */
    public function findAll(): array
    {
        return array_map(fn (array $doc) => ProductDto::fromArray($doc), $this->collection()->find([], ['sort' => ['name' => 1]])->toArray());
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

    public function create(ProductDto $product): ProductDto
    {
        $product->id = (string) new ObjectId();
        $this->collection()->insertOne($this->prepareDocument($product));

        return $product;
    }

    public function update(ProductDto $product): void
    {
        if (!$product->id) {
            throw new \InvalidArgumentException('Product must have an id for update');
        }

        $this->collection()->updateOne(['_id' => new ObjectId($product->id)], ['\$set' => $this->prepareDocument($product)]);
    }

    public function delete(string $id): void
    {
        $this->collection()->deleteOne(['_id' => new ObjectId($id)]);
    }

    /** @deprecated kept for legacy sync usage */
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

    private function prepareDocument(ProductDto $product): array
    {
        $normalizeVariant = static function (VariantDto $variant): array {
            $id = $variant->id ? new ObjectId($variant->id) : new ObjectId();
            $variant->id = (string) $id;

            return [
                '_id' => $id,
                'label' => $variant->label,
                'sku' => $variant->sku,
                'price' => ['ttc' => $variant->priceTtc],
                'isActive' => $variant->isActive,
            ];
        };

        return [
            '_id' => new ObjectId($product->id),
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'brand' => $product->brand,
            'categories' => array_map(static fn (string $id) => new ObjectId($id), $product->categories),
            'variants' => array_map($normalizeVariant, $product->variants),
            'attributes' => $product->attributes,
            'isActive' => $product->isActive,
            'updatedAt' => new \MongoDB\BSON\UTCDateTime((int) (microtime(true) * 1000)),
        ];
    }
}
