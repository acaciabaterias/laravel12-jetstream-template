<?php

declare(strict_types=1);

namespace App\Services\Platform;

class PlatformCurrencyFormattingService
{
    public function __construct(
        private readonly PlatformCurrencyPublicationService $platformCurrencyPublicationService,
    ) {}

    /**
     * @return array{currency_code:string,symbol:string,decimal_scale:int,base_currency_code:string,rate_against_base:float}
     */
    public function contextFor(string $currencyCode): array
    {
        $definitions = (array) config('platform_currencies.supported_currencies', []);
        $activePublication = $this->platformCurrencyPublicationService->activePublication();
        $baseCurrencyCode = (string) ($activePublication?->base_currency_code ?? config('platform_currencies.base_currency', 'BRL'));
        $rateSnapshot = (array) ($activePublication?->rate_snapshot ?? []);
        $definition = (array) ($definitions[$currencyCode] ?? []);
        $rateAgainstBase = $currencyCode === $baseCurrencyCode
            ? 1.0
            : (float) data_get($rateSnapshot, $currencyCode.'.rate_against_base', 1.0);

        return [
            'currency_code' => $currencyCode,
            'symbol' => (string) ($definition['symbol'] ?? $currencyCode),
            'decimal_scale' => (int) ($definition['decimal_scale'] ?? 2),
            'base_currency_code' => $baseCurrencyCode,
            'rate_against_base' => $rateAgainstBase > 0 ? $rateAgainstBase : 1.0,
        ];
    }

    public function convertFromBase(float|int $amount, string $currencyCode): float
    {
        $context = $this->contextFor($currencyCode);

        if ($context['currency_code'] === $context['base_currency_code']) {
            return round((float) $amount, $context['decimal_scale']);
        }

        return round(((float) $amount) / $context['rate_against_base'], $context['decimal_scale']);
    }

    public function formatFromBase(float|int $amount, string $currencyCode): string
    {
        $context = $this->contextFor($currencyCode);
        $convertedAmount = $this->convertFromBase($amount, $currencyCode);

        return format_money_amount($convertedAmount, $context['symbol'], $context['decimal_scale']);
    }
}
