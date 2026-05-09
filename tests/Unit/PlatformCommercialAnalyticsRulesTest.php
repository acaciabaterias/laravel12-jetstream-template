<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SnapshotAnalyticsComercial;
use App\Services\Billing\PlatformCommercialAnalyticsSummaryService;
use Tests\TestCase;

class PlatformCommercialAnalyticsRulesTest extends TestCase
{
    public function test_it_serializes_snapshot_summary_without_losing_precision(): void
    {
        $snapshot = new SnapshotAnalyticsComercial([
            'reference_date' => '2026-05-08',
            'mrr_amount' => '300.00',
            'churn_count' => 1,
            'churn_rate' => '0.3333',
            'delinquent_count' => 2,
            'recovered_count' => 1,
            'recovered_amount' => '150.00',
            'blocked_count' => 1,
        ]);

        $summary = app(PlatformCommercialAnalyticsSummaryService::class)->summarize($snapshot);

        $this->assertSame('2026-05-08', $summary['reference_date']);
        $this->assertSame(300.0, $summary['mrr_amount']);
        $this->assertSame(1, $summary['churn_count']);
        $this->assertSame(0.3333, $summary['churn_rate']);
        $this->assertSame(150.0, $summary['recovered_amount']);
        $this->assertSame(1, $summary['blocked_count']);
    }
}
