<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformLocaleMissingKeyReport;
use App\Models\PlatformLocalePublicationRecord;
use App\Support\Platform\PlatformLocalePublicationStatus;

class PlatformLocaleInspectionService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function inspect(array $filters = []): array
    {
        $activePublication = PlatformLocalePublicationRecord::query()
            ->where('status', PlatformLocalePublicationStatus::Active->value)
            ->latest('published_at')
            ->latest('id')
            ->first();

        $missingKeyReports = PlatformLocaleMissingKeyReport::query()
            ->when(filled($filters['locale'] ?? null), fn ($query) => $query->where('locale_code', $filters['locale']))
            ->when(filled($filters['severity'] ?? null), fn ($query) => $query->where('severity', $filters['severity']))
            ->latest('detected_at')
            ->limit((int) ($filters['limit'] ?? 25))
            ->get();

        $publications = PlatformLocalePublicationRecord::query()
            ->latest('published_at')
            ->latest('id')
            ->limit((int) ($filters['publication_limit'] ?? 10))
            ->get();

        return [
            'summary' => [
                'active_publication_id' => $activePublication?->id,
                'default_locale' => $activePublication?->default_locale ?? config('platform_localization.default_locale'),
                'fallback_locale' => $activePublication?->fallback_locale ?? config('platform_localization.fallback_locale'),
                'supported_locales' => $activePublication?->supported_locales ?? array_keys((array) config('platform_localization.supported_locales', [])),
                'open_missing_keys' => PlatformLocaleMissingKeyReport::query()->where('resolution_status', 'open')->count(),
            ],
            'coverage' => collect((array) $activePublication?->coverage_snapshot)
                ->map(fn (array $coverage, string $locale): array => array_merge(['locale' => $locale], $coverage))
                ->values()
                ->all(),
            'publications' => $publications,
            'missing_key_reports' => $missingKeyReports,
        ];
    }
}
