<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformLocaleMissingKeyReport;
use App\Models\PlatformLocalePublicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformLocaleMissingKeyReport>
 */
class PlatformLocaleMissingKeyReportFactory extends Factory
{
    protected $model = PlatformLocaleMissingKeyReport::class;

    public function definition(): array
    {
        return [
            'platform_locale_publication_record_id' => PlatformLocalePublicationRecord::factory(),
            'locale_code' => 'es',
            'translation_key' => 'Go to ERP login',
            'context_group' => 'auth',
            'severity' => 'critical',
            'resolution_status' => 'open',
            'detected_at' => now(),
            'metadata' => ['source' => 'test'],
        ];
    }
}
