<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformLocalePublicationRecord;
use App\Support\Platform\PlatformLocalePublicationStatus;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PlatformLocalePublicationService
{
    public function __construct(
        private readonly PlatformLocaleCoverageService $platformLocaleCoverageService,
        private readonly PlatformLocalePublicationRules $platformLocalePublicationRules,
        private readonly PlatformLocalizationEventPublisher $platformLocalizationEventPublisher,
    ) {}

    /**
     * @param  array<int, string>  $supportedLocales
     */
    public function publish(array $supportedLocales, string $defaultLocale, string $fallbackLocale, ?int $operatorId): PlatformLocalePublicationRecord
    {
        $coverageSnapshot = $this->platformLocaleCoverageService->snapshot($supportedLocales);
        $validation = $this->platformLocalePublicationRules->validate($supportedLocales, $defaultLocale, $fallbackLocale, $coverageSnapshot);

        if (! $validation['passed']) {
            throw new RuntimeException(implode(' ', $validation['messages']));
        }

        /** @var ConnectionInterface $connection */
        $connection = PlatformLocalePublicationRecord::query()->getModel()->getConnection();

        return $connection->transaction(function () use ($supportedLocales, $defaultLocale, $fallbackLocale, $coverageSnapshot, $operatorId): PlatformLocalePublicationRecord {
            $activePublications = PlatformLocalePublicationRecord::query()
                ->where('status', PlatformLocalePublicationStatus::Active->value)
                ->get();

            $publication = PlatformLocalePublicationRecord::query()->create([
                'release_key' => sprintf('core-i18n-%s', now()->format('Y-m-d-His')),
                'status' => PlatformLocalePublicationStatus::Active->value,
                'default_locale' => $defaultLocale,
                'fallback_locale' => $fallbackLocale,
                'supported_locales' => $supportedLocales,
                'coverage_snapshot' => $coverageSnapshot,
                'published_by' => $operatorId,
                'published_at' => now(),
                'metadata' => [
                    'open_missing_keys' => count($this->platformLocaleCoverageService->flattenMissingReports($coverageSnapshot)),
                ],
            ]);

            foreach ($activePublications as $activePublication) {
                $activePublication->forceFill([
                    'status' => PlatformLocalePublicationStatus::Superseded->value,
                    'superseded_by_publication_id' => $publication->id,
                ])->save();
            }

            foreach ($this->platformLocaleCoverageService->flattenMissingReports($coverageSnapshot) as $report) {
                $publication->missingKeyReports()->create($report);
            }

            $this->platformLocalizationEventPublisher->publish(
                'LOCALIZACAO_PLATAFORMA_PUBLICADA',
                [
                    'publication_id' => $publication->id,
                    'release_key' => $publication->release_key,
                    'default_locale' => $publication->default_locale,
                    'fallback_locale' => $publication->fallback_locale,
                    'supported_locales' => $publication->supported_locales,
                    'status' => $publication->status->value,
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'published_by' => $operatorId,
                        'open_missing_keys' => $publication->missingKeyReports()->count(),
                    ],
                ],
                config('platform_localization.events.default_consumers', ['platform', 'observability']),
            );

            return $publication->refresh();
        });
    }

    public function activePublication(): ?PlatformLocalePublicationRecord
    {
        if (! Schema::connection('central')->hasTable('platform_locale_publication_records')) {
            return null;
        }

        return PlatformLocalePublicationRecord::query()
            ->where('status', PlatformLocalePublicationStatus::Active->value)
            ->latest('published_at')
            ->latest('id')
            ->first();
    }
}
