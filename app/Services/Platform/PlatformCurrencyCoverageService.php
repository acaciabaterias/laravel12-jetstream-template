<?php

declare(strict_types=1);

namespace App\Services\Platform;

class PlatformCurrencyCoverageService
{
    /**
     * @param  array<int, string>  $supportedCurrencies
     * @param  array<string, float|int|string>  $exchangeRates
     * @return array<string, mixed>
     */
    public function snapshot(array $supportedCurrencies, array $exchangeRates, string $baseCurrency): array
    {
        $requiredCurrencies = collect((array) config('platform_currencies.required_conversion_groups', []))
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $currencies = [];
        $configuredPairs = 0;
        $missingPairs = [];

        foreach ($requiredCurrencies as $currencyCode) {
            $isSupported = in_array($currencyCode, $supportedCurrencies, true);
            $configured = $currencyCode === $baseCurrency
                ? $isSupported
                : $isSupported && (float) ($exchangeRates[$currencyCode] ?? 0) > 0;

            if ($configured) {
                $configuredPairs++;
            } else {
                $missingPairs[] = $currencyCode;
            }

            $currencies[$currencyCode] = [
                'required' => true,
                'supported' => $isSupported,
                'configured' => $configured,
                'rate_against_base' => $currencyCode === $baseCurrency
                    ? 1.0
                    : (float) ($exchangeRates[$currencyCode] ?? 0),
            ];
        }

        foreach ($supportedCurrencies as $currencyCode) {
            if (array_key_exists($currencyCode, $currencies)) {
                continue;
            }

            $currencies[$currencyCode] = [
                'required' => false,
                'supported' => true,
                'configured' => $currencyCode === $baseCurrency || (float) ($exchangeRates[$currencyCode] ?? 0) > 0,
                'rate_against_base' => $currencyCode === $baseCurrency
                    ? 1.0
                    : (float) ($exchangeRates[$currencyCode] ?? 0),
            ];
        }

        $requiredPairs = count($requiredCurrencies);

        return [
            'required_pairs' => $requiredPairs,
            'configured_pairs' => $configuredPairs,
            'missing_pairs' => array_values($missingPairs),
            'coverage_ratio' => $requiredPairs === 0 ? 1.0 : round($configuredPairs / $requiredPairs, 4),
            'currencies' => $currencies,
        ];
    }

    /**
     * @param  array<int, string>  $supportedCurrencies
     * @param  array<string, float|int|string>  $exchangeRates
     * @return array<int, array<string, mixed>>
     */
    public function issueReports(array $supportedCurrencies, array $exchangeRates, string $baseCurrency, array $coverageSnapshot): array
    {
        $reports = [];

        foreach ($coverageSnapshot['missing_pairs'] as $currencyCode) {
            $reports[] = [
                'currency_code' => $currencyCode,
                'issue_type' => 'coverage_gap',
                'severity' => 'warning',
                'resolution_status' => 'open',
                'detected_at' => now(),
                'metadata' => [
                    'base_currency_code' => $baseCurrency,
                    'supported_currencies' => $supportedCurrencies,
                ],
            ];
        }

        foreach ($supportedCurrencies as $currencyCode) {
            if ($currencyCode === $baseCurrency) {
                continue;
            }

            $rate = (float) ($exchangeRates[$currencyCode] ?? 0);

            if ($rate <= 0) {
                $reports[] = [
                    'currency_code' => $currencyCode,
                    'issue_type' => 'invalid_rate',
                    'severity' => 'critical',
                    'resolution_status' => 'open',
                    'detected_at' => now(),
                    'metadata' => [
                        'rate_against_base' => $exchangeRates[$currencyCode] ?? null,
                        'base_currency_code' => $baseCurrency,
                    ],
                ];
            }
        }

        return $reports;
    }
}
