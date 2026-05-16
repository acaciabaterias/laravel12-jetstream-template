<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ExecutiveAnalyticsSnapshot;
use App\Models\ExecutiveReportDefinition;
use App\Services\Billing\ExecutiveReportArtifactService;
use Tests\NonDatabaseTestCase;

class ExecutiveReportingExportRulesTest extends NonDatabaseTestCase
{
    public function test_it_builds_consistent_artifact_basename_and_report_lines(): void
    {
        $snapshot = new ExecutiveAnalyticsSnapshot([
            'period_start' => '2026-05-01',
            'period_end' => '2026-05-30',
            'kpi_payload' => ['mrr' => 1200.50, 'active_subscriptions' => 4],
            'drilldown_payload' => [
                'plans' => [['label' => 'Plano Enterprise', 'subscriptions' => 2, 'mrr' => 900.00]],
                'channels' => [['label' => 'pix', 'invoices' => 3, 'amount' => 700.00]],
            ],
        ]);
        $definition = new ExecutiveReportDefinition([
            'slug' => 'executive-overview',
            'name' => 'Executive reporting hub',
        ]);

        $service = app(ExecutiveReportArtifactService::class);
        $lines = $service->linesForSnapshot($snapshot, $definition);

        $this->assertSame('executive-overview-report-12', $service->buildArtifactBasename('executive-overview', 12));
        $this->assertStringContainsString('Executive reporting hub', $lines[0]);
        $this->assertTrue(collect($lines)->contains(fn (string $line): bool => str_contains($line, 'Plano Enterprise')));
    }
}
