<?php

namespace App\Service\Stock;

use App\Entity\MouvementStock;
use App\Infrastructure\Mongo\MongoCatalogClient;
use MongoDB\Collection;

final class StockService
{
    public function __construct(
        private MongoCatalogClient $mongo,
    ) {}

    private function stocks(): Collection
    {
        return $this->mongo->stocks();
    }

    /**
     * Retourne la quantité en stock pour un produit/variante/magasin
     */
    public function getStock(string $productId, ?string $variantId, int $magasinId): int
    {
        $doc = $this->stocks()->findOne([
            'productId' => $productId,
            'variantId' => $variantId,
            'magasinId' => $magasinId,
        ]);

        return $doc ? (int) ($doc['quantity'] ?? 0) : 0;
    }

    /**
     * Fixe la quantité (upsert)
     */
    public function setStock(string $productId, ?string $variantId, int $magasinId, int $quantity): void
    {
        $this->stocks()->updateOne(
            [
                'productId' => $productId,
                'variantId' => $variantId,
                'magasinId' => $magasinId,
            ],
            [
                '$set' => [
                    'quantity' => $quantity,
                ],
            ],
            ['upsert' => true]
        );
    }

    /**
     * Incrémente/décrémente la quantité (delta peut être négatif)
     */
    public function incrementStock(string $productId, ?string $variantId, int $magasinId, int|float $delta): void
    {
        $this->stocks()->updateOne(
            [
                'productId' => $productId,
                'variantId' => $variantId,
                'magasinId' => $magasinId,
            ],
            [
                '$inc' => [
                    'quantity' => (float) $delta,
                ],
            ],
            ['upsert' => true]
        );
    }

    /**
     * Applique un mouvement de stock (IN, OUT, ADJUST, LOSS)
     * Utilisé depuis MouvementStockCrudController::persistEntity/updateEntity
     */
    public function applyMovement(MouvementStock $mvt): void
    {
        $productId  = $mvt->getProductIdMongo();       // id Mongo du produit (string)
        $variantId  = null;                            // pour l’instant, variante unique
        $magasinId  = $mvt->getMagasin()?->getId() ?? 0;
        $quantity   = (float) $mvt->getQuantity();

        if ($magasinId === 0) {
            return; // sécurité si magasin absent
        }

        // ADJUST = on fixe la quantité (dans ce cas, quantity = nouvelle valeur)
        if ($mvt->isAdjust()) {
            $this->setStock($productId, $variantId, $magasinId, (int) $quantity);
            return;
        }

        // IN / OUT / LOSS → incrément
        $delta = $quantity;
        if ($mvt->isExit() || $mvt->isLoss()) {
            $delta = -$quantity;
        }

        $this->incrementStock($productId, $variantId, $magasinId, $delta);
    }

    /**
     * Annule un mouvement (utilisé lors de l’édition/suppression)
     */
    public function revertMovement(MouvementStock $mvt): void
    {
        $productId  = $mvt->getProductIdMongo();
        $variantId  = null;
        $magasinId  = $mvt->getMagasin()?->getId() ?? 0;
        $quantity   = (float) $mvt->getQuantity();

        if ($magasinId === 0) {
            return;
        }

        if ($mvt->isAdjust()) {
            // Cas simple : on ne "revert" pas un ajustement, on laisse la logique métier le gérer
            return;
        }

        // Inverse du applyMovement pour IN / OUT / LOSS
        $delta = -$quantity;
        if ($mvt->isExit() || $mvt->isLoss()) {
            $delta = +$quantity;
        }

        $this->incrementStock($productId, $variantId, $magasinId, $delta);
    }
}
