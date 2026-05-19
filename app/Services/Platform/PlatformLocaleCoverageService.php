<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Support\Platform\PlatformLocaleMissingKeyResolutionStatus;
use App\Support\Platform\PlatformLocaleMissingKeySeverity;

class PlatformLocaleCoverageService
{
    /**
     * @param  array<int, string>  $locales
     * @return array<string, array<string, mixed>>
     */
    public function snapshot(array $locales): array
    {
        $requiredGroups = (array) config('platform_localization.required_translation_groups', []);
        $snapshot = [];

        foreach ($locales as $locale) {
            $requiredKeys = [];
            $missingKeys = [];

            foreach ($requiredGroups as $group => $keys) {
                foreach ((array) $keys as $translationKey) {
                    $requiredKeys[] = $translationKey;

                    if (! $this->hasTranslation($locale, $translationKey)) {
                        $missingKeys[] = [
                            'translation_key' => $translationKey,
                            'context_group' => $group,
                            'severity' => $this->severityForGroup($group)->value,
                        ];
                    }
                }
            }

            $requiredCount = count($requiredKeys);
            $missingCount = count($missingKeys);

            $snapshot[$locale] = [
                'required_keys' => $requiredCount,
                'translated_keys' => $requiredCount - $missingCount,
                'missing_keys' => $missingKeys,
                'coverage_ratio' => $requiredCount === 0 ? 1.0 : round(($requiredCount - $missingCount) / $requiredCount, 4),
            ];
        }

        return $snapshot;
    }

    /**
     * @param  array<string, array<string, mixed>>  $snapshot
     * @return array<int, array<string, mixed>>
     */
    public function flattenMissingReports(array $snapshot): array
    {
        $reports = [];

        foreach ($snapshot as $locale => $coverage) {
            foreach ((array) ($coverage['missing_keys'] ?? []) as $missingKey) {
                $reports[] = [
                    'locale_code' => $locale,
                    'translation_key' => $missingKey['translation_key'],
                    'context_group' => $missingKey['context_group'],
                    'severity' => $missingKey['severity'],
                    'resolution_status' => PlatformLocaleMissingKeyResolutionStatus::Open->value,
                    'detected_at' => now(),
                    'metadata' => [
                        'coverage_ratio' => $coverage['coverage_ratio'] ?? 0,
                    ],
                ];
            }
        }

        return $reports;
    }

    private function hasTranslation(string $locale, string $translationKey): bool
    {
        $path = lang_path(sprintf('%s.json', $locale));

        if (! is_file($path)) {
            return false;
        }

        /** @var array<string, string>|null $translations */
        $translations = json_decode((string) file_get_contents($path), true);

        return is_array($translations) && array_key_exists($translationKey, $translations);
    }

    private function severityForGroup(string $group): PlatformLocaleMissingKeySeverity
    {
        return in_array($group, ['auth', 'navigation'], true)
            ? PlatformLocaleMissingKeySeverity::Critical
            : PlatformLocaleMissingKeySeverity::Warning;
    }
}
