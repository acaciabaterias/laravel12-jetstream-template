<?php

declare(strict_types=1);

namespace App\Services\Platform;

class PlatformCurrencyResolutionRules
{
    /**
     * @param  array<int, string>  $supportedCurrencies
     */
    public function resolve(?string $preferredCurrency, array $supportedCurrencies, string $defaultCurrency, string $baseCurrency): string
    {
        if ($preferredCurrency !== null && in_array($preferredCurrency, $supportedCurrencies, true)) {
            return $preferredCurrency;
        }

        if (in_array($defaultCurrency, $supportedCurrencies, true)) {
            return $defaultCurrency;
        }

        if (in_array($baseCurrency, $supportedCurrencies, true)) {
            return $baseCurrency;
        }

        return 'BRL';
    }
}
