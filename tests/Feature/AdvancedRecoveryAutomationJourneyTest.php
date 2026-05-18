<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Services\Billing\AdvancedRecoveryAutomationDispatchScheduler;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedRecoveryAutomationJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('advanced_revenue_recovery_automation.events.publish_to_backbone', false);

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

    public function test_it_schedules_one_valid_next_automated_action_and_reuses_the_same_dispatch(): void
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
            'vencimento' => now()->subDays(4)->toDateString(),
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
        RecoveryAutomationPolicyVersion::factory()->create([
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'scope_filters' => [
                'severity' => ['medium'],
                'revenue_recovery_policy_id' => $policy->id,
            ],
            'guardrail_rules' => [
                'max_dispatches_per_day' => 3,
                'cooldown_hours' => 24,
            ],
            'fallback_matrix' => [
                'stage_channels' => [
                    'd1' => ['whatsapp', 'email'],
                ],
                'templates' => [
                    'd1' => [
                        'whatsapp' => 'recovery-d1-whatsapp',
                    ],
                ],
            ],
        ]);

        $firstDispatch = app(AdvancedRecoveryAutomationDispatchScheduler::class)->schedule($case);
        $secondDispatch = app(AdvancedRecoveryAutomationDispatchScheduler::class)->schedule($case->fresh());

        $this->assertNotNull($firstDispatch);
        $this->assertSame($firstDispatch?->id, $secondDispatch?->id);
        $this->assertDatabaseHas('recovery_automation_journeys', [
            'caso_recuperacao_receita_id' => $case->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Paused->value,
            'current_stage' => 'd1',
            'current_channel' => 'whatsapp',
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_dispatches', [
            'id' => $firstDispatch?->id,
            'channel' => 'whatsapp',
            'stage_key' => 'd1',
            'dispatch_status' => 'scheduled',
        ], 'central');
        $this->assertDatabaseHas('acoes_recuperacao_receita', [
            'id' => $firstDispatch?->acao_recuperacao_receita_id,
            'channel' => 'whatsapp',
            'stage_name' => 'd1',
            'status' => 'scheduled',
        ], 'central');
    }
}
