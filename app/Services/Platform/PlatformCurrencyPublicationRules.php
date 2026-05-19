<?php

declare(strict_types=1);

namespace App\Services\Platform;

class PlatformCurrencyPublicationRules
{
    /**
     * @param  array<int, string>  $supportedCurrencies
     * @param  array<string, float|int|string>  $exchangeRates
     * @return array{passed:bool,messages:array<int,string>}
     */
    public function validate(
        array $supportedCurrencies,
        string $baseCurrency,
        string $defaultCurrency,
        array $exchangeRates,
        array $coverageSnapshot,
    ): array {
        $messages = [];
        $knownCurrencies = array_keys((array) config('platform_currencies.supported_currencies', []));

        if (! in_array($baseCurrency, $supportedCurrencies, true)) {
            $messages[] = 'A moeda base precisa fazer parte da publicacao.';
        }

        if (! in_array($defaultCurrency, $supportedCurrencies, true)) {
            $messages[] = 'A moeda padrao precisa fazer parte da publicacao.';
        }

        foreach ($supportedCurrencies as $currencyCode) {
            if (! in_array($currencyCode, $knownCurrencies, true)) {
                $messages[] = sprintf('A moeda %s nao pertence ao catalogo suportado.', $currencyCode);
            }

            if ($currencyCode === $baseCurrency) {
                continue;
            }

            if ((float) ($exchangeRates[$currencyCode] ?? 0) <= 0) {
                $messages[] = sprintf('A moeda %s precisa de taxa positiva em relacao a %s.', $currencyCode, $baseCurrency);
            }
        }

        if ((float) ($coverageSnapshot['coverage_ratio'] ?? 0) <= 0) {
            $messages[] = 'A publicacao precisa cobrir pelo menos um recorte monetario obrigatorio.';
        }

        return [
            'passed' => $messages === [],
            'messages' => $messages,
        ];
    }
}
