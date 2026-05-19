<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PlatformCurrencyIssueReport;
use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Tests\Concerns\InteractsWithPlatformCurrencySetup;
use Tests\TestCase;

class PlatformCurrencyInspectionTest extends TestCase
{
    use InteractsWithPlatformCurrencySetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformCurrencyMigrations();
    }

    public function test_inspection_endpoint_returns_summary_rates_and_filtered_issues(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $publication = PlatformCurrencyPublicationRecord::factory()->create([
            'status' => PlatformCurrencyPublicationStatus::Active->value,
        ]);
        $publication->rateEntries()->create([
            'currency_code' => 'USD',
            'rate_against_base' => 5.42000000,
            'inverse_rate' => 0.18450185,
            'effective_at' => now(),
            'metadata' => ['source' => 'test'],
        ]);
        PlatformCurrencyIssueReport::factory()->create([
            'platform_currency_publication_record_id' => $publication->id,
            'currency_code' => 'EUR',
            'severity' => 'critical',
        ]);

        $response = $this
            ->actingAs($billing, 'platform')
            ->getJson(route('admin.currencies.inspection', [
                'currency' => 'EUR',
                'severity' => 'critical',
            ]));

        $response
            ->assertOk()
            ->assertJsonPath('summary.base_currency_code', $publication->base_currency_code)
            ->assertJsonPath('issues.0.currency_code', 'EUR')
            ->assertJsonPath('issues.0.severity', 'critical');
    }
}
