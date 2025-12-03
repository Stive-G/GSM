<?php

namespace App\Dto\Catalog;

class MagasinDto
{
    public function __construct(
        public ?string $id = null,
        public string $name = '',
        public string $code = '',
        public bool $isActive = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['_id']) ? (string) $data['_id'] : ($data['id'] ?? null),
            $data['name'] ?? '',
            $data['code'] ?? '',
            (bool) ($data['isActive'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            '_id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'isActive' => $this->isActive,
        ];
    }
}
