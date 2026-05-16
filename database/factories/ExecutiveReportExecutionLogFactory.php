<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExecutiveReportExecutionLog;
use App\Models\ExecutiveReportExport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExecutiveReportExecutionLog>
 */
class ExecutiveReportExecutionLogFactory extends Factory
{
    protected $model = ExecutiveReportExecutionLog::class;

    public function definition(): array
    {
        return [
            'executive_report_export_id' => ExecutiveReportExport::factory(),
            'event_type' => 'completed',
            'operator_name' => fake()->name(),
            'operator_id' => 1,
            'event_payload' => ['source' => 'factory'],
            'logged_at' => now(),
        ];
    }
}
