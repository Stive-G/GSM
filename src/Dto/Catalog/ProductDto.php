<?php

namespace App\Dto\Catalog;

class ProductDto
{
    /** @var string[] */
    public array $categories = [];
    /** @var VariantDto[] */
    public array $variants = [];

    public function __construct(
        public ?string $id = null,
        public string $name = '',
        public string $slug = '',
        public ?string $description = null,
        public ?string $brand = null,
        public bool $isActive = true,
        public array $attributes = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        $dto = new self(
            isset($data['_id']) ? (string) $data['_id'] : ($data['id'] ?? null),
            $data['name'] ?? '',
            $data['slug'] ?? '',
            $data['description'] ?? null,
            $data['brand'] ?? null,
            (bool) ($data['isActive'] ?? true),
            $data['attributes'] ?? [],
        );

        $dto->categories = array_map('strval', $data['categories'] ?? []);
        $dto->variants = array_map(fn (array $variant) => VariantDto::fromArray($variant), $data['variants'] ?? []);

        return $dto;
    }

    public function toArray(): array
    {
        return [
            '_id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'brand' => $this->brand,
            'categories' => $this->categories,
            'variants' => array_map(fn (VariantDto $variant) => $variant->toArray(), $this->variants),
            'attributes' => $this->attributes,
            'isActive' => $this->isActive,
        ];
    }
}
