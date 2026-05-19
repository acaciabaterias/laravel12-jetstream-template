<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FiscalCfopCatalogEntry;
use App\Models\FiscalOperationScenario;
use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRuleMapping;
use App\Models\FiscalRulePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Services\Fiscal\PlatformFiscalRuleEventPublisher;
use App\Support\Fiscal\FiscalRuleIssueResolutionStatus;
use App\Support\Fiscal\FiscalRuleIssueSeverity;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalRuleFoundationTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_fiscal_rules.events.publish_to_backbone', true);

        $this->runPlatformFiscalRuleMigrations(includeBackbone: true);
    }

    public function test_platform_fiscal_rule_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('fiscal_cfop_catalog_entries'));
        $this->assertTrue(Schema::connection('central')->hasTable('fiscal_operation_scenarios'));
        $this->assertTrue(Schema::connection('central')->hasTable('fiscal_rule_publication_records'));
        $this->assertTrue(Schema::connection('central')->hasTable('fiscal_rule_mappings'));
        $this->assertTrue(Schema::connection('central')->hasTable('fiscal_rule_issue_reports'));
    }

    public function test_platform_fiscal_models_persist_relationships_and_enum_casts(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $cfop = FiscalCfopCatalogEntry::factory()->create([
            'cfop_code' => '7101',
        ]);
        $scenario = FiscalOperationScenario::factory()->create([
            'scenario_key' => 'direct_export',
        ]);
        $publication = FiscalRulePublicationRecord::factory()->create([
            'status' => FiscalRulePublicationStatus::Active->value,
            'published_by' => $operator->id,
        ]);
        $mapping = FiscalRuleMapping::factory()->create([
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => $scenario->scenario_key,
            'cfop_code' => $cfop->cfop_code,
        ]);
        $issueReport = FiscalRuleIssueReport::factory()->create([
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => $scenario->scenario_key,
            'resolved_by' => $operator->id,
            'severity' => FiscalRuleIssueSeverity::Critical->value,
            'resolution_status' => FiscalRuleIssueResolutionStatus::Resolved->value,
        ]);

        $this->assertSame('central', $publication->getConnectionName());
        $this->assertSame($publication->id, $mapping->publication->id);
        $this->assertSame($publication->id, $issueReport->publication->id);
        $this->assertSame($operator->id, $publication->publisher->id);
        $this->assertSame(FiscalRulePublicationStatus::Active, $publication->status);
        $this->assertSame(FiscalRuleIssueSeverity::Critical, $issueReport->severity);
        $this->assertSame(FiscalRuleIssueResolutionStatus::Resolved, $issueReport->resolution_status);
    }

    public function test_platform_fiscal_permissions_are_restricted_to_billing_roles(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $support = UsuarioPlataforma::factory()->create();

        $this->assertTrue(Gate::forUser($billing)->allows('manage-platform-fiscal-rules'));
        $this->assertFalse(Gate::forUser($support)->allows('manage-platform-fiscal-rules'));
    }

    public function test_platform_fiscal_event_publisher_creates_contract_and_central_outbox_record(): void
    {
        Queue::fake();

        app(PlatformFiscalRuleEventPublisher::class)->publish(
            'CATALOGO_FISCAL_PUBLICADO',
            [
                'publication_id' => 1,
                'release_key' => 'fiscal-2026-05-18-110000',
                'supported_scenarios' => ['direct_export', 'resale_import'],
                'status' => 'active',
            ],
            ['platform', 'fiscal', 'observability'],
        );

        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'CATALOGO_FISCAL_PUBLICADO',
            'producer' => 'platform-fiscal-rules',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'CATALOGO_FISCAL_PUBLICADO',
            'tenant_external_ref' => 'platform-central',
            'origin_context' => 'platform-fiscal-rules',
        ], 'central');
    }
}
