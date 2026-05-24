<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRulePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Fiscal\FiscalRuleIssueSeverity;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalInspectionTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformFiscalRuleMigrations();
    }

    public function test_inspection_endpoint_returns_filtered_publications_and_issue_summary(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $publication = FiscalRulePublicationRecord::factory()->create([
            'status' => FiscalRulePublicationStatus::Active->value,
            'release_key' => 'fiscal-inspection-001',
            'published_at' => now(),
        ]);
        FiscalRuleIssueReport::factory()->create([
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => 'direct_export',
            'severity' => FiscalRuleIssueSeverity::Critical->value,
            'issue_type' => 'tax_profile_gap',
        ]);

        $response = $this
            ->actingAs($billing, 'platform')
            ->getJson(route('admin.fiscal-rules.inspection', [
                'scenario' => 'direct_export',
                'severity' => 'critical',
                'status' => 'active',
                'issue_type' => 'tax_profile_gap',
            ]));

        $response
            ->assertOk()
            ->assertJsonPath('summary.active_publications', 1)
            ->assertJsonPath('summary.critical_issues', 1)
            ->assertJsonPath('summary.material_tax_issues', 1)
            ->assertJsonPath('issues.0.scenario_key', 'direct_export')
            ->assertJsonPath('issues.0.issue_type', 'tax_profile_gap')
            ->assertJsonPath('publications.0.release_key', 'fiscal-inspection-001');
    }
}
