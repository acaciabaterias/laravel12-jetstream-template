<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\RecoveryAutomationDispatch;
use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\AdvancedRevenueRecoveryAutomationEventPublisher;
use App\Support\Billing\RecoveryAutomationDispatchStatus;
use App\Support\Billing\RecoveryAutomationExperimentStatus;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use App\Support\Billing\RecoveryAutomationViolationSeverity;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoveryPolicyStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdvancedRecoveryAutomationFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('advanced_revenue_recovery_automation.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
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

    public function test_advanced_recovery_automation_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('recovery_automation_policy_versions'));
        $this->assertTrue(Schema::connection('central')->hasTable('recovery_automation_journeys'));
        $this->assertTrue(Schema::connection('central')->hasTable('recovery_automation_dispatches'));
        $this->assertTrue(Schema::connection('central')->hasTable('recovery_automation_experiments'));
        $this->assertTrue(Schema::connection('central')->hasTable('recovery_automation_violations'));
    }

    public function test_advanced_recovery_models_persist_relationships_and_enum_casts(): void
    {
        $cliente = Cliente::factory()->create();
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
        ]);
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $policy = PoliticaRecuperacaoReceita::factory()->create([
            'status' => RevenueRecoveryPolicyStatus::Active->value,
        ]);
        $case = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'severity' => RevenueRecoverySeverity::Medium->value,
        ]);
        $policyVersion = RecoveryAutomationPolicyVersion::factory()->create([
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'created_by' => $operator->id,
        ]);
        $experiment = RecoveryAutomationExperiment::factory()->create([
            'recovery_automation_policy_version_id' => $policyVersion->id,
            'status' => RecoveryAutomationExperimentStatus::Running->value,
            'created_by' => $operator->id,
        ]);
        $journey = RecoveryAutomationJourney::factory()->create([
            'caso_recuperacao_receita_id' => $case->id,
            'recovery_automation_policy_version_id' => $policyVersion->id,
            'recovery_automation_experiment_id' => $experiment->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
        ]);
        $action = AcaoRecuperacaoReceita::factory()->create([
            'caso_recuperacao_receita_id' => $case->id,
        ]);
        $dispatch = RecoveryAutomationDispatch::factory()->create([
            'recovery_automation_journey_id' => $journey->id,
            'acao_recuperacao_receita_id' => $action->id,
            'dispatch_status' => RecoveryAutomationDispatchStatus::Dispatched->value,
            'operator_id' => $operator->id,
        ]);
        $violation = RecoveryAutomationViolation::factory()->create([
            'recovery_automation_policy_version_id' => $policyVersion->id,
            'recovery_automation_journey_id' => $journey->id,
            'recovery_automation_dispatch_id' => $dispatch->id,
            'severity' => RecoveryAutomationViolationSeverity::High->value,
            'resolved_by' => $operator->id,
        ]);

        $this->assertSame('central', $policyVersion->getConnectionName());
        $this->assertSame($case->id, $journey->recoveryCase->id);
        $this->assertSame($policyVersion->id, $experiment->policyVersion->id);
        $this->assertSame($dispatch->id, $journey->dispatches()->firstOrFail()->id);
        $this->assertSame($violation->id, $policyVersion->violations()->firstOrFail()->id);
        $this->assertSame(RecoveryAutomationPolicyStatus::Active, $policyVersion->status);
        $this->assertSame(RecoveryAutomationExperimentStatus::Running, $experiment->status);
        $this->assertSame(RecoveryAutomationJourneyStatus::Active, $journey->journey_status);
        $this->assertSame(RecoveryAutomationDispatchStatus::Dispatched, $dispatch->dispatch_status);
        $this->assertSame(RecoveryAutomationViolationSeverity::High, $violation->severity);
    }

    public function test_advanced_recovery_permissions_are_restricted_to_platform_billing_roles(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $support = UsuarioPlataforma::factory()->create();

        $this->assertTrue(Gate::forUser($billing)->allows('manage-advanced-revenue-recovery-automation'));
        $this->assertFalse(Gate::forUser($support)->allows('manage-advanced-revenue-recovery-automation'));
    }

    public function test_advanced_recovery_event_publisher_creates_contract_and_central_outbox_record(): void
    {
        Queue::fake();

        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-automation',
        ]);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
        ]);
        $policy = PoliticaRecuperacaoReceita::factory()->create();
        $case = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
        ])->loadMissing('cliente');

        app(AdvancedRevenueRecoveryAutomationEventPublisher::class)->publish(
            eventType: 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA',
            recoveryCase: $case,
            payload: [
                'policy_version_id' => 1,
                'journey_id' => 1,
                'status' => 'active',
            ],
            consumers: ['platform', 'recovery', 'analytics'],
            schemaDefinition: [
                'policy_version_id' => 'integer',
                'journey_id' => 'integer',
                'status' => 'string',
            ],
        );

        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA',
            'producer' => 'advanced-revenue-recovery-automation',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA',
            'tenant_external_ref' => 'tenant-automation',
            'origin_context' => 'advanced-revenue-recovery-automation',
        ], 'central');
    }
}
