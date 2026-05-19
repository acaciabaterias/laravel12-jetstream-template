<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformCurrencyIssueReport;
use App\Models\PlatformCurrencyPublicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformCurrencyIssueReport>
 */
class PlatformCurrencyIssueReportFactory extends Factory
{
    protected $model = PlatformCurrencyIssueReport::class;

    public function definition(): array
    {
        return [
            'platform_currency_publication_record_id' => PlatformCurrencyPublicationRecord::factory(),
            'currency_code' => 'EUR',
            'issue_type' => 'coverage_gap',
            'severity' => 'critical',
            'resolution_status' => 'open',
            'detected_at' => now(),
            'metadata' => ['source' => 'test'],
        ];
    }
}
