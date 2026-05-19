<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FiscalOperationScenario;
use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRulePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Fiscal\FiscalRuleIssueSeverity;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalFallbackTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformFiscalRuleMigrations();
    }

    public function test_missing_mapping_falls_back_to_the_governed_cfop_response_without_breaking_consultation(): void
    {
        FiscalOperationScenario::factory()->create([
            'scenario_key' => 'resale_import',
            'display_name' => 'Resale import',
            'operation_direction' => 'import',
        ]);
        FiscalRulePublicationRecord::factory()->create([
            'status' => FiscalRulePublicationStatus::Active->value,
            'release_key' => 'fiscal-fallback-001',
            'supported_scenarios' => ['direct_export'],
            'published_at' => now(),
        ]);
        FiscalRuleIssueReport::factory()->create([
            'scenario_key' => 'resale_import',
            'issue_type' => 'missing_mapping',
            'severity' => FiscalRuleIssueSeverity::Critical->value,
        ]);

        $operator = UsuarioPlataforma::factory()->billing()->create();

        $response = $this
            ->actingAs($operator, 'platform')
            ->getJson(route('admin.fiscal-rules.inspection', ['scenario' => 'resale_import']));

        $response
            ->assertOk()
            ->assertJsonPath('lookup.resolution_type', 'governed_fallback')
            ->assertJsonPath('lookup.cfop_code', '3101')
            ->assertJsonPath('lookup.issue.code', 'missing_mapping')
            ->assertJsonPath('issues.0.scenario_key', 'resale_import');
    }
}
