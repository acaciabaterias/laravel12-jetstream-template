<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Services\Billing\AdvancedRecoveryAutomationExperimentService;
use App\Support\Billing\RecoveryAutomationExperimentStatus;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedRecoveryAutomationExperimentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
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

    public function test_it_persists_holdout_and_variant_assignments_for_eligible_journeys(): void
    {
        [$firstCase, $policy] = $this->makeCase('tenant-a');
        [$secondCase] = $this->makeCase('tenant-b');

        $policyVersion = RecoveryAutomationPolicyVersion::factory()->create([
            'status' => RecoveryAutomationPolicyStatus::Active->value,
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
            'recovery_automation_policy_version_id' => $policyVersion->id,
            'status' => RecoveryAutomationExperimentStatus::Running->value,
            'allocation_rules' => [
                'forced_assignments' => [
                    (string) $firstCase->id => 'holdout',
                    (string) $secondCase->id => 'variant_b',
                ],
            ],
            'variant_definitions' => [
                'variant_a' => ['channel' => 'whatsapp'],
                'variant_b' => ['channel' => 'email'],
                'holdout' => ['holdout' => true],
            ],
        ]);
        $firstJourney = RecoveryAutomationJourney::factory()->create([
            'caso_recuperacao_receita_id' => $firstCase->id,
            'recovery_automation_policy_version_id' => $policyVersion->id,
            'recovery_automation_experiment_id' => null,
            'variant_key' => 'default',
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
        ]);
        $secondJourney = RecoveryAutomationJourney::factory()->create([
            'caso_recuperacao_receita_id' => $secondCase->id,
            'recovery_automation_policy_version_id' => $policyVersion->id,
            'recovery_automation_experiment_id' => null,
            'variant_key' => 'default',
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
        ]);

        $service = app(AdvancedRecoveryAutomationExperimentService::class);
        $assignedHoldout = $service->assignJourney($firstJourney);
        $assignedVariant = $service->assignJourney($secondJourney);
        $replayedVariant = $service->assignJourney($secondJourney->fresh());

        $this->assertSame('holdout', $assignedHoldout->variant_key);
        $this->assertSame(RecoveryAutomationJourneyStatus::Paused, $assignedHoldout->journey_status);
        $this->assertSame('variant_b', $assignedVariant->variant_key);
        $this->assertSame($assignedVariant->variant_key, $replayedVariant->variant_key);
        $this->assertDatabaseHas('recovery_automation_journeys', [
            'id' => $firstJourney->id,
            'recovery_automation_experiment_id' => $experiment->id,
            'variant_key' => 'holdout',
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_journeys', [
            'id' => $secondJourney->id,
            'recovery_automation_experiment_id' => $experiment->id,
            'variant_key' => 'variant_b',
        ], 'central');
    }

    /**
     * @return array{0: CasoRecuperacaoReceita, 1?: PoliticaRecuperacaoReceita}
     */
    private function makeCase(string $subdomain): array
    {
        $cliente = Cliente::factory()->create(['subdominio' => $subdomain]);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
        ]);
        $policy = PoliticaRecuperacaoReceita::factory()->create([
            'stage_definitions' => [
                ['name' => 'd1', 'channel' => 'whatsapp', 'delay_hours' => 0],
            ],
        ]);
        $case = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'current_stage' => 'd1',
            'severity' => RevenueRecoverySeverity::Medium->value,
        ]);

        return [$case, $policy];
    }
}
