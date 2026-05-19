<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FiscalRulePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Services\Fiscal\PlatformFiscalPublicationService;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalCoverageTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformFiscalRuleMigrations();
    }

    public function test_degraded_publication_records_missing_required_scenarios_without_replacing_the_last_healthy_bundle(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        FiscalRulePublicationRecord::factory()->create([
            'status' => FiscalRulePublicationStatus::Active->value,
            'release_key' => 'healthy-fiscal-bundle',
            'published_at' => now()->subMinute(),
        ]);

        $publication = app(PlatformFiscalPublicationService::class)->publish(
            [
                ['cfop_code' => '7101', 'description' => 'Direct export of own production', 'operation_direction' => 'export'],
                ['cfop_code' => '3101', 'description' => 'Import for resale', 'operation_direction' => 'import'],
            ],
            [
                ['scenario_key' => 'direct_export', 'cfop_code' => '7101', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_ncm']],
                ['scenario_key' => 'resale_import', 'cfop_code' => '3101', 'classification_code' => '85072010', 'operation_direction' => 'import', 'validation_flags' => ['requires_customs_record']],
            ],
            $billing->id,
        );

        $this->assertSame('draft', $publication->status->value);
        $this->assertSame(0.5, $publication->coverage_snapshot['coverage_ratio']);
        $this->assertDatabaseHas('fiscal_rule_issue_reports', [
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => 'indirect_export',
            'issue_type' => 'missing_scenario',
            'resolution_status' => 'open',
        ], 'central');
        $this->assertDatabaseHas('fiscal_rule_publication_records', [
            'release_key' => 'healthy-fiscal-bundle',
            'status' => 'active',
        ], 'central');
    }
}
