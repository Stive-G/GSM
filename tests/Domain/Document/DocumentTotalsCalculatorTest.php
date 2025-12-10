<?php

namespace App\Tests\Domain\Document;

use App\Domain\Document\DocumentTotalsCalculator;
use PHPUnit\Framework\TestCase;

class DocumentTotalsCalculatorTest extends TestCase
{
    private DocumentTotalsCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new DocumentTotalsCalculator();
    }

    public function testCalculateLineTotalHt(): void
    {
        $result = $this->calculator->calculateLineTotalHt(10.0, 3.0);

        $this->assertSame(30.0, $result);
    }

    public function testCalculateTotalsWithoutDiscount(): void
    {
        $lines = [
            ['unitPriceHt' => 10.0, 'quantity' => 2.0], // 20
            ['unitPriceHt' => 5.0,  'quantity' => 4.0], // 20
        ];

        $totals = $this->calculator->calculateTotals($lines, 20.0); // TVA 20%

        $this->assertSame(40.0, $totals['totalLinesHt']);
        $this->assertSame(0.0,  $totals['totalDiscountLines']);
        $this->assertSame(40.0, $totals['totalAfterLineDisc']);
        $this->assertSame(40.0, $totals['totalHt']);
        $this->assertSame(8.0,  $totals['totalVat']);  // 40 * 0.20
        $this->assertSame(48.0, $totals['totalTtc']);
    }

    public function testCalculateTotalsWithLineDiscount(): void
    {
        $lines = [
            [
                'unitPriceHt'     => 100.0,
                'quantity'        => 1.0,
                'discountPercent' => 10.0, // -10
            ],
            [
                'unitPriceHt'     => 50.0,
                'quantity'        => 2.0,  // 100
                'discountPercent' => 0.0,
            ],
        ];
        // Totaux lignes bruts = 200 (100 + 100)
        // Remise ligne = 10
        // HT après remises lignes = 190
        // TVA 20% => VAT = 38, TTC = 228

        $totals = $this->calculator->calculateTotals($lines, 20.0);

        $this->assertSame(200.0, $totals['totalLinesHt']);
        $this->assertSame(10.0,  $totals['totalDiscountLines']);
        $this->assertSame(190.0, $totals['totalAfterLineDisc']);
        $this->assertSame(190.0, $totals['totalHt']);
        $this->assertSame(38.0,  $totals['totalVat']);
        $this->assertSame(228.0, $totals['totalTtc']);
    }

    public function testCalculateTotalsWithGlobalDiscount(): void
    {
        $lines = [
            ['unitPriceHt' => 50.0, 'quantity' => 2.0], // 100
        ];

        // Remise globale 5% sur 100 => 5
        // HT final = 95
        // TVA 20% => 19
        // TTC = 114
        $totals = $this->calculator->calculateTotals($lines, 20.0, 5.0);

        $this->assertSame(100.0, $totals['totalLinesHt']);
        $this->assertSame(0.0,   $totals['totalDiscountLines']);
        $this->assertSame(100.0, $totals['totalAfterLineDisc']);
        $this->assertSame(5.0,   $totals['globalDiscount']);
        $this->assertSame(95.0,  $totals['totalHt']);
        $this->assertSame(19.0,  $totals['totalVat']);
        $this->assertSame(114.0, $totals['totalTtc']);
    }

    public function testCalculateTotalsWithLineAndGlobalDiscount(): void
    {
        $lines = [
            [
                'unitPriceHt'     => 100.0,
                'quantity'        => 1.0,
                'discountPercent' => 10.0, // -10
            ],
            [
                'unitPriceHt'     => 50.0,
                'quantity'        => 1.0,  // 50
                'discountPercent' => 0.0,
            ],
        ];
        // Totaux lignes bruts = 150
        // Remise lignes = 10 => 140
        // Remise globale 5% sur 140 => 7
        // HT final = 133
        // TVA 20% => 26.6 => arrondi 26.6
        // TTC = 159.6

        $totals = $this->calculator->calculateTotals($lines, 20.0, 5.0);

        $this->assertSame(150.0, $totals['totalLinesHt']);
        $this->assertSame(10.0,  $totals['totalDiscountLines']);
        $this->assertSame(140.0, $totals['totalAfterLineDisc']);
        $this->assertSame(7.0,   $totals['globalDiscount']);
        $this->assertSame(133.0, $totals['totalHt']);
        $this->assertSame(26.6,  $totals['totalVat']);
        $this->assertSame(159.6, $totals['totalTtc']);
    }
}
