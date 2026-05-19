<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRulePublicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalRuleIssueReport>
 */
class FiscalRuleIssueReportFactory extends Factory
{
    protected $model = FiscalRuleIssueReport::class;

    public function definition(): array
    {
        return [
            'fiscal_rule_publication_record_id' => FiscalRulePublicationRecord::factory(),
            'scenario_key' => 'resale_import',
            'issue_type' => 'missing_scenario',
            'severity' => 'critical',
            'resolution_status' => 'open',
            'detected_at' => now(),
            'issue_payload' => ['source' => 'test'],
        ];
    }
}
