<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformLocalePublicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformLocalePublicationRecord>
 */
class PlatformLocalePublicationRecordFactory extends Factory
{
    protected $model = PlatformLocalePublicationRecord::class;

    public function definition(): array
    {
        return [
            'release_key' => fake()->unique()->slug(),
            'status' => 'draft',
            'default_locale' => 'pt_BR',
            'fallback_locale' => 'en',
            'supported_locales' => ['pt_BR', 'en', 'es'],
            'coverage_snapshot' => [
                'pt_BR' => ['required_keys' => 10, 'translated_keys' => 10, 'missing_keys' => [], 'coverage_ratio' => 1.0],
                'en' => ['required_keys' => 10, 'translated_keys' => 10, 'missing_keys' => [], 'coverage_ratio' => 1.0],
                'es' => ['required_keys' => 10, 'translated_keys' => 9, 'missing_keys' => ['Go to ERP login'], 'coverage_ratio' => 0.9],
            ],
            'metadata' => ['source' => 'test'],
        ];
    }
}
