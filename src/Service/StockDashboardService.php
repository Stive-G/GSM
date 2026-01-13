<?php

namespace App\Service;

use App\Infrastructure\Mongo\MongoCatalogClient;

final class StockDashboardService
{
    public function __construct(private readonly MongoCatalogClient $mongo) {}

    public function countLowStocks(int $threshold): int
    {
        return $this->mongo->stocks()->countDocuments([
            'quantity' => ['$lte' => $threshold],
        ]);
    }

    /**
     * Retourne lowStocks au format twig:
     * - s.product.label
     * - s.quantity
     */
    public function findLowStocks(int $threshold, int $limit = 30): array
    {
        $pipeline = $this->buildLowStockPipeline($threshold, $limit);

        return iterator_to_array(
            $this->mongo->stocks()->aggregate($pipeline),
            false
        );
    }

    private function buildLowStockPipeline(int $threshold, int $limit): array
    {
        return [
            ['$match' => [
                'quantity' => ['$lte' => $threshold],
            ]],
            ['$sort' => ['quantity' => 1]],
            ['$limit' => $limit],

            // Si productId est déjà un ObjectId => ok
            // Si productId est une string "66a..." => on tente de convertir
            [
                '$addFields' => [
                    'productObjId' => [
                        '$cond' => [
                            'if' => ['$eq' => [['$type' => '$productId'], 'objectId']],
                            'then' => '$productId',
                            'else' => [
                                '$convert' => [
                                    'input' => '$productId',
                                    'to' => 'objectId',
                                    'onError' => null,
                                    'onNull' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // join product
            ['$lookup' => [
                'from' => 'products',
                'localField' => 'productObjId',
                'foreignField' => '_id',
                'as' => 'product',
            ]],
            ['$unwind' => [
                'path' => '$product',
                'preserveNullAndEmptyArrays' => true,
            ]],

            // projection clean (sans magasin/cond.)
            ['$project' => [
                '_id' => 1,
                'quantity' => 1,
                'product' => [
                    'label' => ['$ifNull' => ['$product.label', 'Produit inconnu']],
                    'sku'   => ['$ifNull' => ['$product.sku', '—']],
                ],
            ]],
        ];
    }
}
