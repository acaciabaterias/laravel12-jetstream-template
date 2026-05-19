<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformCurrencyIssueReport;
use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\PlatformCurrencyRateEntry;
use App\Support\Platform\PlatformCurrencyPublicationStatus;

class PlatformCurrencyInspectionService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function inspect(array $filters = []): array
    {
        $activePublication = PlatformCurrencyPublicationRecord::query()
            ->where('status', PlatformCurrencyPublicationStatus::Active->value)
            ->latest('published_at')
            ->latest('id')
            ->first();

        $issues = PlatformCurrencyIssueReport::query()
            ->when(filled($filters['currency'] ?? null), fn ($query) => $query->where('currency_code', $filters['currency']))
            ->when(filled($filters['severity'] ?? null), fn ($query) => $query->where('severity', $filters['severity']))
            ->latest('detected_at')
            ->limit((int) ($filters['limit'] ?? 25))
            ->get();

        $publications = PlatformCurrencyPublicationRecord::query()
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->latest('published_at')
            ->latest('id')
            ->limit((int) ($filters['publication_limit'] ?? 10))
            ->get();

        $rates = PlatformCurrencyRateEntry::query()
            ->when(
                $activePublication !== null,
                fn ($query) => $query->where('platform_currency_publication_record_id', $activePublication->id),
            )
            ->when(filled($filters['currency'] ?? null), fn ($query) => $query->where('currency_code', $filters['currency']))
            ->latest('effective_at')
            ->get();

        return [
            'summary' => [
                'active_publication_id' => $activePublication?->id,
                'base_currency_code' => $activePublication?->base_currency_code ?? config('platform_currencies.base_currency'),
                'default_currency_code' => $activePublication?->default_currency_code ?? config('platform_currencies.default_currency'),
                'supported_currencies' => $activePublication?->supported_currencies ?? array_keys((array) config('platform_currencies.supported_currencies', [])),
                'open_issues' => PlatformCurrencyIssueReport::query()->where('resolution_status', 'open')->count(),
            ],
            'rates' => $rates,
            'publications' => $publications,
            'issues' => $issues,
        ];
    }
}
