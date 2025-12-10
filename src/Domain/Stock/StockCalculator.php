<?php

namespace App\Domain\Stock;

/**
 * Service de calcul de stock, indépendant de Doctrine.
 * Cela permet de le tester facilement sans base de données.
 */
class StockCalculator
{
    /**
     * Applique un mouvement de stock à une quantité actuelle.
     *
     * @param float $currentQuantity Quantité actuelle en stock
     * @param float $delta Mouvement (positif pour entrée, négatif pour sortie)
     *
     * @return float Nouvelle quantité
     */
    public function applyMovement(float $currentQuantity, float $delta): float
    {
        $newQuantity = $currentQuantity + $delta;

        // On ne permet pas que le stock devienne négatif
        if ($newQuantity < 0) {
            $newQuantity = 0;
        }

        // On peut aussi arrondir pour rester cohérent avec NUMERIC(18,4)
        return round($newQuantity, 4);
    }
}
