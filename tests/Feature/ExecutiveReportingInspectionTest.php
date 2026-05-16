<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Billing\ExecutiveReportExportService;
use Tests\Concerns\InteractsWithExecutiveReportingFixtures;
use Tests\TestCase;

class ExecutiveReportingInspectionTest extends TestCase
{
    use InteractsWithExecutiveReportingFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runExecutiveReportingMigrations();
        $this->seedExecutiveReportingScenario();
    }

    public function test_inspection_endpoint_filters_reports_by_format_and_status(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();

        app(ExecutiveReportExportService::class)->export(['days' => 30], 'excel', $operator->id);
        app(ExecutiveReportExportService::class)->export(['days' => 30], 'pdf', $operator->id);

        $response = $this
            ->actingAs($operator, 'platform')
            ->getJson(route('admin.reports.inspection', ['format' => 'excel', 'status' => 'completed']));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'exports')
            ->assertJsonPath('exports.0.format', 'excel')
            ->assertJsonPath('exports.0.status', 'completed');
    }
}
