<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EventoOutbox;
use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\AdvancedRecoveryAutomationPublicationService;
use App\Services\Billing\AdvancedRecoveryAutomationRollbackService;
use App\Services\Integration\IntegrationStorageManager;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedRecoveryAutomationGovernanceTest extends TestCase
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

    public function test_it_persists_publication_event_payload_with_experiment_metadata(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $policy = RecoveryAutomationPolicyVersion::factory()->create([
            'slug' => 'adaptive-governed-policy',
            'status' => RecoveryAutomationPolicyStatus::Draft->value,
            'scope_filters' => ['severity' => ['medium']],
            'guardrail_rules' => [
                'max_dispatches_per_day' => 3,
                'cooldown_hours' => 24,
                'suppression_hours' => 48,
            ],
            'fallback_matrix' => [
                'stage_channels' => ['d1' => ['whatsapp', 'email']],
                'fallbacks' => ['email'],
            ],
        ]);
        $experiment = RecoveryAutomationExperiment::factory()->create([
            'recovery_automation_policy_version_id' => $policy->id,
            'name' => 'Governed rollout',
            'variant_definitions' => [
                'variant_a' => ['channel' => 'whatsapp'],
                'variant_b' => ['channel' => 'email'],
                'holdout' => ['holdout' => true],
            ],
        ]);

        app(AdvancedRecoveryAutomationPublicationService::class)->publish($policy, $experiment, $operator->id);

        /** @var EventoOutbox $policyEvent */
        $policyEvent = app(IntegrationStorageManager::class)->using('central', function (): EventoOutbox {
            /** @var EventoOutbox|null $event */
            $event = EventoOutbox::query()
                ->where('event_type', 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA')
                ->latest('id')
                ->get()
                ->first(fn (EventoOutbox $outbox): bool => ($outbox->payload['variant_key'] ?? null) === 'experiment_attached');

            return $event ?? throw new ModelNotFoundException;
        });

        $this->assertSame('advanced-revenue-recovery-automation', $policyEvent->origin_context);
        $this->assertSame($policy->id, $policyEvent->payload['policy_version_id']);
        $this->assertSame('adaptive-governed-policy', $policyEvent->payload['policy_slug']);
        $this->assertSame('active', $policyEvent->payload['status']);
        $this->assertSame('experiment_attached', $policyEvent->payload['variant_key']);
        $this->assertSame($experiment->id, $policyEvent->payload['metadata']['experiment_id']);
        $this->assertSame($operator->id, $policyEvent->payload['metadata']['approved_by']);
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA',
            'origin_context' => 'advanced-revenue-recovery-automation',
        ], 'central');
    }

    public function test_it_persists_rollback_audit_context_for_journeys_and_event_payload(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
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
        $journey = RecoveryAutomationJourney::factory()->create([
            'recovery_automation_policy_version_id' => $candidate->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
        ]);
        RecoveryAutomationViolation::factory()->create([
            'recovery_automation_policy_version_id' => $candidate->id,
            'violation_type' => 'performance_regression',
            'severity' => 'critical',
            'resolution_status' => 'open',
        ]);

        app(AdvancedRecoveryAutomationRollbackService::class)->rollback(
            $candidate,
            'Conversao abaixo do baseline governado.',
            $operator->id,
        );

        $journey->refresh();

        /** @var array<string, mixed> $rollbackContext */
        $rollbackContext = $journey->metadata['rollback_context'];
        $this->assertSame($candidate->id, $rollbackContext['rolled_back_from_policy_version_id']);
        $this->assertSame($baseline->id, $rollbackContext['restored_policy_version_id']);
        $this->assertSame('Conversao abaixo do baseline governado.', $rollbackContext['reason']);
        $this->assertNotNull($journey->rollback_marked_at);

        /** @var EventoOutbox $rollbackEvent */
        $rollbackEvent = app(IntegrationStorageManager::class)->using('central', fn (): EventoOutbox => EventoOutbox::query()
            ->where('event_type', 'ROLLBACK_AUTOMACAO_RECUPERACAO_EXECUTADO')
            ->latest('id')
            ->firstOrFail());

        $this->assertSame($candidate->id, $rollbackEvent->payload['policy_version_id']);
        $this->assertSame('rolled_back', $rollbackEvent->payload['status']);
        $this->assertSame($baseline->id, $rollbackEvent->payload['metadata']['restored_policy_version_id']);
        $this->assertSame(1, $rollbackEvent->payload['metadata']['affected_journeys']);
        $this->assertSame('Conversao abaixo do baseline governado.', $rollbackEvent->payload['metadata']['reason']);
    }
}
