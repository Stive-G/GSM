<?php

namespace App\Dto\Catalog;

class StockDto
{
    public function __construct(
        public ?string $productId = null,
        public ?string $variantId = null,
        public ?string $magasinId = null,
        public float $quantity = 0.0,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $updatedAt = $data['updatedAt'] ?? null;
        if ($updatedAt instanceof \MongoDB\BSON\UTCDateTime) {
            $updatedAt = $updatedAt->toDateTime();
        } elseif (is_string($updatedAt)) {
            $updatedAt = new \DateTimeImmutable($updatedAt);
        }

        return new self(
            isset($data['productId']) ? (string) $data['productId'] : null,
            isset($data['variantId']) ? (string) $data['variantId'] : null,
            isset($data['magasinId']) ? (string) $data['magasinId'] : null,
            (float) ($data['quantity'] ?? 0),
            $updatedAt instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($updatedAt) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'productId' => $this->productId,
            'variantId' => $this->variantId,
            'magasinId' => $this->magasinId,
            'quantity' => $this->quantity,
            'updatedAt' => ($this->updatedAt ?? new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }
}
