<?php

namespace App\Dto\Catalog;

class CategoryDto
{
    public function __construct(
        public ?string $id = null,
        public string $name = '',
        public string $slug = '',
        public ?string $parentId = null,
        public int $position = 0,
        public bool $isActive = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['_id']) ? (string) $data['_id'] : ($data['id'] ?? null),
            $data['name'] ?? '',
            $data['slug'] ?? '',
            isset($data['parentId']) ? (string) $data['parentId'] : null,
            (int) ($data['position'] ?? 0),
            (bool) ($data['isActive'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            '_id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'parentId' => $this->parentId,
            'position' => $this->position,
            'isActive' => $this->isActive,
        ];
    }
}
