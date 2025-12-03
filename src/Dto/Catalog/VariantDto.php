<?php

namespace App\Dto\Catalog;

class VariantDto
{
    public function __construct(
        public ?string $id = null,
        public string $label = '',
        public ?string $sku = null,
        public float $priceTtc = 0.0,
        public bool $isActive = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['_id']) ? (string) $data['_id'] : ($data['id'] ?? null),
            $data['label'] ?? '',
            $data['sku'] ?? null,
            (float) ($data['price']['ttc'] ?? $data['priceTtc'] ?? 0),
            (bool) ($data['isActive'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            '_id' => $this->id,
            'label' => $this->label,
            'sku' => $this->sku,
            'price' => [
                'ttc' => $this->priceTtc,
            ],
            'isActive' => $this->isActive,
        ];
    }
}
