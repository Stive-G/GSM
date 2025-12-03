<?php

namespace App\Repository\Catalog;

use MongoDB\Collection;
use MongoDB\Database;

abstract class AbstractMongoCatalogRepository
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    abstract protected function getCollection(): Collection;

    protected function collection(): Collection
    {
        return $this->getCollection();
    }
}
