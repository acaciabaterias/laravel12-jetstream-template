<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\DrilldownAnalyticsComercial;
use App\Models\SnapshotAnalyticsComercial;
use App\Services\Billing\PlatformCommercialAnalyticsInspectionService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformCommercialAnalyticsDrilldownRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_08_220105_create_central_snapshot_analytics_comercials_table.php',
            'database/migrations/central/2026_05_08_220109_create_central_drilldown_analytics_comercials_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_it_serializes_filtered_drilldown_rows_for_inspection(): void
    {
        $snapshot = SnapshotAnalyticsComercial::factory()->create();
        DrilldownAnalyticsComercial::factory()->create([
            'snapshot_analytics_comercial_id' => $snapshot->id,
            'metric_key' => 'recovery',
            'metric_value' => 420.50,
        ]);

        $payload = app(PlatformCommercialAnalyticsInspectionService::class)->inspect([
            'snapshot_id' => $snapshot->id,
            'metric_key' => 'recovery',
        ]);

        $this->assertCount(1, $payload['drilldowns']);
        $this->assertSame('recovery', $payload['drilldowns'][0]['metric_key']);
        $this->assertSame(420.5, $payload['drilldowns'][0]['metric_value']);
    }
}
