<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\AdvancedRecoveryAutomationManager;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class AdvancedRecoveryAutomationRollbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('advanced_revenue_recovery_automation.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
            'database/migrations/central/2026_05_16_130000_create_central_advanced_revenue_recovery_automation_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_super_admin_can_rollback_a_degraded_policy_and_mark_affected_journeys(): void
    {
        $superAdmin = UsuarioPlataforma::factory()->create(['papel' => 'super_admin']);
        $baseline = RecoveryAutomationPolicyVersion::factory()->create([
            'slug' => 'baseline-safe',
            'status' => RecoveryAutomationPolicyStatus::Superseded->value,
            'activation_completed_at' => now()->subDay(),
        ]);
        $candidate = RecoveryAutomationPolicyVersion::factory()->create([
            'slug' => 'candidate-regressed',
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'activation_completed_at' => now(),
        ]);
        $activeJourney = RecoveryAutomationJourney::factory()->create([
            'recovery_automation_policy_version_id' => $candidate->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
        ]);
        $pausedJourney = RecoveryAutomationJourney::factory()->create([
            'recovery_automation_policy_version_id' => $candidate->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Paused->value,
        ]);
        $completedJourney = RecoveryAutomationJourney::factory()->create([
            'recovery_automation_policy_version_id' => $candidate->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Completed->value,
        ]);
        RecoveryAutomationViolation::factory()->create([
            'recovery_automation_policy_version_id' => $candidate->id,
            'violation_type' => 'performance_regression',
            'severity' => 'critical',
            'resolution_status' => 'open',
        ]);

        $this->actingAs($superAdmin, 'platform');

        Livewire::test(AdvancedRecoveryAutomationManager::class)
            ->set('rollbackReason', 'Conversao e SLA abaixo do baseline aprovado.')
            ->call('rollbackPolicy', $candidate->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recovery_automation_policy_versions', [
            'id' => $candidate->id,
            'status' => RecoveryAutomationPolicyStatus::RolledBack->value,
            'rolled_back_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_policy_versions', [
            'id' => $baseline->id,
            'status' => RecoveryAutomationPolicyStatus::Active->value,
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_journeys', [
            'id' => $activeJourney->id,
            'journey_status' => RecoveryAutomationJourneyStatus::RolledBack->value,
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_journeys', [
            'id' => $pausedJourney->id,
            'journey_status' => RecoveryAutomationJourneyStatus::RolledBack->value,
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_journeys', [
            'id' => $completedJourney->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Completed->value,
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_violations', [
            'recovery_automation_policy_version_id' => $candidate->id,
            'resolution_status' => 'rolled_back',
            'resolved_by' => $superAdmin->id,
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ROLLBACK_AUTOMACAO_RECUPERACAO_EXECUTADO',
            'origin_context' => 'advanced-revenue-recovery-automation',
        ], 'central');
    }
}
