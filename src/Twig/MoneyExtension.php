<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class MoneyExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $currency,
        private readonly string $currencyLabel
    ) {}

    public function getFilters(): array
    {
        return [ new TwigFilter('money', [$this, 'format']) ];
    }

    public function format(null|int|float|string $amount): string
    {
        if ($amount === null || $amount === '') return '—';

        $n = (float) $amount;

        // FCFA (XAF) : pas de décimales
        if ($this->currency === 'XAF') {
            return number_format($n, 0, ' ', ' ') . ' ' . $this->currencyLabel; // 12 500 FCFA
        }

        // EUR : 2 décimales + €
        return number_format($n, 2, ',', ' ') . ' €'; // 12 500,00 €
    }
}
