<?php

namespace App\Infrastructure\Mongo;

use MongoDB\Client;
use MongoDB\Collection;

/**
 * Accès centralisé aux collections MongoDB du catalogue GSM.
 */
final class MongoCatalogClient
{
    public function __construct(
        private readonly Client $client,
        private readonly string $dbName
    ) {}

    private function db()
    {
        return $this->client->selectDatabase($this->dbName);
    }

    public function products(): Collection
    {
        return $this->db()->selectCollection('products');
    }

    public function categories(): Collection
    {
        return $this->db()->selectCollection('categories');
    }

    public function stocks(): Collection
    {
        return $this->db()->selectCollection('stocks');
    }
}
