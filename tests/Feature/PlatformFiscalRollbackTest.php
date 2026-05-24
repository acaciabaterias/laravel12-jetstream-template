<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformFiscalRuleManager;
use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRulePublicationRecord;
use App\Models\UsuarioPlataforma;
use App\Support\Fiscal\FiscalRuleIssueSeverity;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalRollbackTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_fiscal_rules.events.publish_to_backbone', true);

        $this->runPlatformFiscalRuleMigrations(includeBackbone: true);
    }

    public function test_super_admin_can_rollback_a_degraded_active_publication_and_restore_the_previous_bundle(): void
    {
        $superAdmin = UsuarioPlataforma::factory()->create(['papel' => 'super_admin']);
        $baseline = FiscalRulePublicationRecord::factory()->create([
            'release_key' => 'fiscal-baseline-safe',
            'status' => FiscalRulePublicationStatus::Superseded->value,
            'published_at' => now()->subDay(),
        ]);
        $candidate = FiscalRulePublicationRecord::factory()->create([
            'release_key' => 'fiscal-candidate-regressed',
            'status' => FiscalRulePublicationStatus::Active->value,
            'published_at' => now(),
        ]);
        FiscalRuleIssueReport::factory()->create([
            'fiscal_rule_publication_record_id' => $candidate->id,
            'scenario_key' => 'resale_import',
            'issue_type' => 'direction_mismatch',
            'severity' => FiscalRuleIssueSeverity::Critical->value,
            'resolution_status' => 'open',
        ]);

        $this->actingAs($superAdmin, 'platform');

        Livewire::test(PlatformFiscalRuleManager::class)
            ->set('rollbackReason', 'Inconsistencia critica de enquadramento fiscal.')
            ->call('rollbackPublication', $candidate->id)
            ->assertHasNoErrors();

        $candidate->refresh();

        $this->assertDatabaseHas('fiscal_rule_publication_records', [
            'id' => $candidate->id,
            'status' => FiscalRulePublicationStatus::RolledBack->value,
            'rolled_back_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('fiscal_rule_publication_records', [
            'id' => $baseline->id,
            'status' => FiscalRulePublicationStatus::Active->value,
        ], 'central');
        $this->assertDatabaseHas('fiscal_rule_issue_reports', [
            'fiscal_rule_publication_record_id' => $candidate->id,
            'resolution_status' => 'rolled_back',
            'resolved_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ROLLBACK_CATALOGO_FISCAL_EXECUTADO',
            'origin_context' => 'platform-fiscal-rules',
        ], 'central');
        $this->assertSame(['direction_mismatch'], $candidate->metadata['rollback']['critical_issue_types']);
        $this->assertSame(0, $candidate->metadata['rollback']['material_tax_issue_count']);
    }
}
