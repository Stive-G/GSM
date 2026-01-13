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
     * Retourne la liste lowStocks au format attendu par Twig:
     * s.article.label / s.conditionnement.label / s.magasin.name / s.quantity
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
            // 1) low stock
            ['$match' => [
                'quantity' => ['$lte' => $threshold],
            ]],
            // 2) ordre
            ['$sort' => ['quantity' => 1]],
            // 3) limite
            ['$limit' => $limit],

            // 4) join product -> article
            ['$lookup' => [
                'from' => 'products',
                'localField' => 'productId',
                'foreignField' => '_id',
                'as' => 'article',
            ]],
            ['$unwind' => [
                'path' => '$article',
                'preserveNullAndEmptyArrays' => true,
            ]],

            // 5) join conditionnement
            ['$lookup' => [
                'from' => 'conditionnements',
                'localField' => 'conditionnementId',
                'foreignField' => '_id',
                'as' => 'conditionnement',
            ]],
            ['$unwind' => [
                'path' => '$conditionnement',
                'preserveNullAndEmptyArrays' => true,
            ]],

            // 6) join magasin
            ['$lookup' => [
                'from' => 'magasins',
                'localField' => 'magasinId',
                'foreignField' => '_id',
                'as' => 'magasin',
            ]],
            ['$unwind' => [
                'path' => '$magasin',
                'preserveNullAndEmptyArrays' => true,
            ]],

            // 7) projection clean
            ['$project' => [
                '_id' => 1,
                'quantity' => 1,
                'article' => [
                    'label' => ['$ifNull' => ['$article.label', 'Produit inconnu']],
                ],
                'conditionnement' => [
                    'label' => ['$ifNull' => ['$conditionnement.label', null]],
                ],
                'magasin' => [
                    'name' => ['$ifNull' => ['$magasin.name', '—']],
                ],
            ]],
        ];
    }
}
