<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\CompromissoPagamento;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Services\Billing\AdvancedRecoveryAutomationDispatchScheduler;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedRecoveryAutomationFallbackTest extends TestCase
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

    public function test_it_falls_back_to_the_next_available_channel_when_the_primary_one_is_blocked(): void
    {
        [$case, $policy] = $this->makeRecoveryCase([
            'blocked_channels' => ['whatsapp'],
        ]);

        RecoveryAutomationPolicyVersion::factory()->create([
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'scope_filters' => [
                'severity' => ['medium'],
                'revenue_recovery_policy_id' => $policy->id,
            ],
            'fallback_matrix' => [
                'stage_channels' => [
                    'd1' => ['whatsapp', 'email', 'manual_follow_up'],
                ],
                'templates' => [
                    'd1' => [
                        'email' => 'recovery-d1-email',
                    ],
                ],
            ],
        ]);

        $dispatch = app(AdvancedRecoveryAutomationDispatchScheduler::class)->schedule($case);

        $this->assertNotNull($dispatch);
        $this->assertSame('email', $dispatch?->channel);
        $this->assertSame('primary_channel_blocked', $dispatch?->fallback_reason);
        $this->assertDatabaseHas('acoes_recuperacao_receita', [
            'id' => $dispatch?->acao_recuperacao_receita_id,
            'channel' => 'email',
        ], 'central');
    }

    public function test_it_suppresses_new_dispatches_while_an_active_payment_promise_is_open(): void
    {
        [$case, $policy] = $this->makeRecoveryCase();

        RecoveryAutomationPolicyVersion::factory()->create([
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'scope_filters' => [
                'severity' => ['medium'],
                'revenue_recovery_policy_id' => $policy->id,
            ],
            'fallback_matrix' => [
                'stage_channels' => [
                    'd1' => ['whatsapp', 'email'],
                ],
            ],
        ]);

        CompromissoPagamento::factory()->create([
            'caso_recuperacao_receita_id' => $case->id,
            'status' => 'open',
            'suspends_until' => now()->addDays(2),
        ]);

        $dispatch = app(AdvancedRecoveryAutomationDispatchScheduler::class)->schedule($case);

        $this->assertNull($dispatch);
        $this->assertDatabaseCount('recovery_automation_dispatches', 0, 'central');
        $this->assertDatabaseHas('recovery_automation_journeys', [
            'caso_recuperacao_receita_id' => $case->id,
            'journey_status' => 'paused',
        ], 'central');
    }

    /**
     * @param  array<string, mixed>  $caseMetadata
     * @return array{0: CasoRecuperacaoReceita, 1: PoliticaRecuperacaoReceita}
     */
    private function makeRecoveryCase(array $caseMetadata = []): array
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
            'vencimento' => now()->subDays(3)->toDateString(),
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
            'metadata' => $caseMetadata,
        ]);

        return [$case, $policy];
    }
}
