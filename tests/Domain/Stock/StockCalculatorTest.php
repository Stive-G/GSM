<?php

namespace App\Tests\Domain\Stock;

use App\Domain\Stock\StockCalculator;
use PHPUnit\Framework\TestCase;

class StockCalculatorTest extends TestCase
{
    private StockCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new StockCalculator();
    }

    public function testPositiveMovementIncreasesStock(): void
    {
        $current = 10.0;
        $delta   = 5.5;

        $result = $this->calculator->applyMovement($current, $delta);

        $this->assertSame(15.5, $result);
    }

    public function testNegativeMovementDecreasesStock(): void
    {
        $current = 20.0;
        $delta   = -7.25;

        $result = $this->calculator->applyMovement($current, $delta);

        $this->assertSame(12.75, $result);
    }

    public function testStockCannotBecomeNegative(): void
    {
        $current = 5.0;
        $delta   = -10.0;

        $result = $this->calculator->applyMovement($current, $delta);

        $this->assertSame(0.0, $result);
    }

    public function testResultIsRoundedToFourDecimals(): void
    {
        $current = 1.11111;
        $delta   = 2.22229; // 1.11111 + 2.22229 = 3.33340

        $result = $this->calculator->applyMovement($current, $delta);

        $this->assertSame(3.3334, $result);
    }
}
