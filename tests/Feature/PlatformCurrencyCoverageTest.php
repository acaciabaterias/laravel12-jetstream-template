<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Platform\PlatformCurrencyPublicationService;
use Tests\Concerns\InteractsWithPlatformCurrencySetup;
use Tests\TestCase;

class PlatformCurrencyCoverageTest extends TestCase
{
    use InteractsWithPlatformCurrencySetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformCurrencyMigrations();
    }

    public function test_publication_generates_coverage_snapshot_and_issue_reports_for_missing_required_currency(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();

        $publication = app(PlatformCurrencyPublicationService::class)->publish(
            ['BRL', 'USD'],
            'BRL',
            'USD',
            ['USD' => '5.42'],
            $billing->id,
        );

        $this->assertSame(0.6667, $publication->coverage_snapshot['coverage_ratio']);
        $this->assertDatabaseHas('platform_currency_issue_reports', [
            'platform_currency_publication_record_id' => $publication->id,
            'currency_code' => 'EUR',
            'issue_type' => 'coverage_gap',
            'resolution_status' => 'open',
        ], 'central');
    }
}
