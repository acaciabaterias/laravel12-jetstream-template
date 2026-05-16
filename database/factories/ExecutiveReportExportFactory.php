<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExecutiveAnalyticsSnapshot;
use App\Models\ExecutiveReportDefinition;
use App\Models\ExecutiveReportExport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExecutiveReportExport>
 */
class ExecutiveReportExportFactory extends Factory
{
    protected $model = ExecutiveReportExport::class;

    public function definition(): array
    {
        return [
            'executive_analytics_snapshot_id' => ExecutiveAnalyticsSnapshot::factory(),
            'executive_report_definition_id' => ExecutiveReportDefinition::factory(),
            'format' => 'excel',
            'export_status' => 'completed',
            'requested_at' => now(),
            'completed_at' => now(),
            'scope_summary' => 'Periodo padrao',
            'file_reference' => 'executive-reports/report.xlsx',
            'metadata' => ['source' => 'factory'],
        ];
    }
}
