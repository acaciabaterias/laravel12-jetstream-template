<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformCurrencyPublicationRecord;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PlatformCurrencyPublicationService
{
    public function __construct(
        private readonly PlatformCurrencyCoverageService $platformCurrencyCoverageService,
        private readonly PlatformCurrencyPublicationRules $platformCurrencyPublicationRules,
        private readonly PlatformCurrencyEventPublisher $platformCurrencyEventPublisher,
    ) {}

    /**
     * @param  array<int, string>  $supportedCurrencies
     * @param  array<string, float|int|string>  $exchangeRates
     */
    public function publish(
        array $supportedCurrencies,
        string $baseCurrency,
        string $defaultCurrency,
        array $exchangeRates,
        ?int $operatorId,
    ): PlatformCurrencyPublicationRecord {
        $coverageSnapshot = $this->platformCurrencyCoverageService->snapshot($supportedCurrencies, $exchangeRates, $baseCurrency);
        $validation = $this->platformCurrencyPublicationRules->validate(
            $supportedCurrencies,
            $baseCurrency,
            $defaultCurrency,
            $exchangeRates,
            $coverageSnapshot,
        );

        if (! $validation['passed']) {
            throw new RuntimeException(implode(' ', $validation['messages']));
        }

        $issueReports = $this->platformCurrencyCoverageService->issueReports(
            $supportedCurrencies,
            $exchangeRates,
            $baseCurrency,
            $coverageSnapshot,
        );

        /** @var ConnectionInterface $connection */
        $connection = PlatformCurrencyPublicationRecord::query()->getModel()->getConnection();

        return $connection->transaction(function () use (
            $supportedCurrencies,
            $baseCurrency,
            $defaultCurrency,
            $exchangeRates,
            $coverageSnapshot,
            $issueReports,
            $operatorId,
        ): PlatformCurrencyPublicationRecord {
            $activePublications = PlatformCurrencyPublicationRecord::query()
                ->where('status', PlatformCurrencyPublicationStatus::Active->value)
                ->get();

            $publication = PlatformCurrencyPublicationRecord::query()->create([
                'release_key' => sprintf('fx-%s', now()->format('Y-m-d-His')),
                'status' => PlatformCurrencyPublicationStatus::Active->value,
                'base_currency_code' => $baseCurrency,
                'default_currency_code' => $defaultCurrency,
                'supported_currencies' => $supportedCurrencies,
                'rate_snapshot' => $this->buildRateSnapshot($supportedCurrencies, $exchangeRates, $baseCurrency),
                'coverage_snapshot' => $coverageSnapshot,
                'published_by' => $operatorId,
                'published_at' => now(),
                'metadata' => [
                    'open_issues' => count($issueReports),
                ],
            ]);

            foreach ($activePublications as $activePublication) {
                $activePublication->forceFill([
                    'status' => PlatformCurrencyPublicationStatus::Superseded->value,
                    'superseded_by_publication_id' => $publication->id,
                ])->save();
            }

            foreach ($supportedCurrencies as $currencyCode) {
                $rateAgainstBase = $currencyCode === $baseCurrency
                    ? 1.0
                    : (float) ($exchangeRates[$currencyCode] ?? 0);

                $publication->rateEntries()->create([
                    'currency_code' => $currencyCode,
                    'rate_against_base' => $rateAgainstBase,
                    'inverse_rate' => $rateAgainstBase > 0 ? round(1 / $rateAgainstBase, 8) : null,
                    'effective_at' => now(),
                    'metadata' => ['source' => 'publication'],
                ]);
            }

            foreach ($issueReports as $report) {
                $publication->issueReports()->create($report);
            }

            $this->platformCurrencyEventPublisher->publish(
                'MOEDAS_PLATAFORMA_PUBLICADAS',
                [
                    'publication_id' => $publication->id,
                    'release_key' => $publication->release_key,
                    'base_currency_code' => $publication->base_currency_code,
                    'default_currency_code' => $publication->default_currency_code,
                    'supported_currencies' => $publication->supported_currencies,
                    'status' => $publication->status->value,
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'published_by' => $operatorId,
                        'open_issues' => $publication->issueReports()->count(),
                    ],
                ],
                config('platform_currencies.events.default_consumers', ['platform', 'billing', 'analytics']),
            );

            return $publication->refresh();
        });
    }

    public function activePublication(): ?PlatformCurrencyPublicationRecord
    {
        if (! Schema::connection('central')->hasTable('platform_currency_publication_records')) {
            return null;
        }

        return PlatformCurrencyPublicationRecord::query()
            ->where('status', PlatformCurrencyPublicationStatus::Active->value)
            ->latest('published_at')
            ->latest('id')
            ->first();
    }

    /**
     * @param  array<int, string>  $supportedCurrencies
     * @param  array<string, float|int|string>  $exchangeRates
     * @return array<string, array<string, string>>
     */
    private function buildRateSnapshot(array $supportedCurrencies, array $exchangeRates, string $baseCurrency): array
    {
        $snapshot = [];

        foreach ($supportedCurrencies as $currencyCode) {
            $rateAgainstBase = $currencyCode === $baseCurrency
                ? 1.0
                : (float) ($exchangeRates[$currencyCode] ?? 0);

            $snapshot[$currencyCode] = [
                'rate_against_base' => number_format($rateAgainstBase, 8, '.', ''),
                'inverse_rate' => number_format($rateAgainstBase > 0 ? 1 / $rateAgainstBase : 0, 8, '.', ''),
            ];
        }

        return $snapshot;
    }
}
