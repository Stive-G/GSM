<?php

namespace App\Domain\Document;

/**
 * Service de calcul des totaux d'un document (HT, TVA, TTC, remises).
 *
 * On ne dépend pas des entités Doctrine : on travaille sur des tableaux simples,
 * ce qui rend le service très simple à tester.
 */
class DocumentTotalsCalculator
{
    /**
     * Calcule le total HT d'une ligne.
     */
    public function calculateLineTotalHt(float $unitPriceHt, float $quantity): float
    {
        return round($unitPriceHt * $quantity, 4);
    }

    /**
     * Calcule les totaux d'un document.
     *
     * @param array $lines [
     *      [
     *          'unitPriceHt'      => float,
     *          'quantity'         => float,
     *          'discountPercent'  => float|null (remise ligne, ex: 10 pour 10%),
     *      ],
     *      ...
     * ]
     * @param float      $tvaRatePercent      Taux de TVA en pourcentage (ex: 20 pour 20%)
     * @param float|null $globalDiscountPercent Remise globale sur le total HT (ex: 5 pour 5%)
     *
     * @return array [
     *      'totalLinesHt'        => float,  // total HT avant remise globale
     *      'totalDiscountLines'  => float,  // total des remises de lignes
     *      'totalAfterLineDisc'  => float,  // HT après remises lignes
     *      'globalDiscount'      => float,  // montant remise globale
     *      'totalHt'             => float,  // HT final
     *      'totalVat'            => float,  // montant TVA
     *      'totalTtc'            => float,  // TTC final
     * ]
     */
    public function calculateTotals(array $lines, float $tvaRatePercent, ?float $globalDiscountPercent = null): array
    {
        $totalLinesHt       = 0.0;
        $totalDiscountLines = 0.0;

        foreach ($lines as $line) {
            $unitPriceHt = (float) ($line['unitPriceHt'] ?? 0);
            $quantity    = (float) ($line['quantity'] ?? 0);
            $discountPct = isset($line['discountPercent']) ? (float) $line['discountPercent'] : null;

            $lineTotal = $this->calculateLineTotalHt($unitPriceHt, $quantity);
            $totalLinesHt += $lineTotal;

            if ($discountPct !== null && $discountPct > 0) {
                $discountAmount = $lineTotal * ($discountPct / 100);
                $totalDiscountLines += $discountAmount;
            }
        }

        $totalAfterLineDisc = $totalLinesHt - $totalDiscountLines;

        // Remise globale
        $globalDiscount = 0.0;
        if ($globalDiscountPercent !== null && $globalDiscountPercent > 0) {
            $globalDiscount = $totalAfterLineDisc * ($globalDiscountPercent / 100);
        }

        $totalHt = $totalAfterLineDisc - $globalDiscount;

        // TVA / TTC
        $tvaRate = $tvaRatePercent / 100;
        $totalVat = $totalHt * $tvaRate;
        $totalTtc = $totalHt + $totalVat;

        // On arrondit à 2 décimales pour les montants finaux
        return [
            'totalLinesHt'       => round($totalLinesHt, 2),
            'totalDiscountLines' => round($totalDiscountLines, 2),
            'totalAfterLineDisc' => round($totalAfterLineDisc, 2),
            'globalDiscount'     => round($globalDiscount, 2),
            'totalHt'            => round($totalHt, 2),
            'totalVat'           => round($totalVat, 2),
            'totalTtc'           => round($totalTtc, 2),
        ];
    }
}
