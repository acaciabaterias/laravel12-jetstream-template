<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformRevenueRecoveryOpenCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_revenue_recovery.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_command_opens_revenue_recovery_case_for_overdue_invoice_and_schedules_first_action(): void
    {
        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-recovery-open',
        ]);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'status' => 'pending',
            'vencimento' => now()->subDays(2)->toDateString(),
        ]);
        PoliticaRecuperacaoReceita::factory()->create([
            'stage_definitions' => [
                ['name' => 'd1', 'channel' => 'email', 'delay_hours' => 0],
            ],
        ]);

        $this->artisan('platform-revenue-recovery:evaluate', [
            'invoice_id' => $fatura->id,
            '--reason' => 'invoice_overdue',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('casos_recuperacao_receita', [
            'fatura_saas_id' => $fatura->id,
            'entry_reason' => 'invoice_overdue',
            'current_stage' => 'd1',
            'status' => 'open',
        ], 'central');
        $this->assertDatabaseHas('acoes_recuperacao_receita', [
            'stage_name' => 'd1',
            'channel' => 'email',
            'status' => 'scheduled',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'RECUPERACAO_RECEITA_INICIADA',
            'tenant_external_ref' => 'tenant-recovery-open',
            'origin_context' => 'platform-revenue-recovery',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ACAO_COBRANCA_AGENDADA',
            'tenant_external_ref' => 'tenant-recovery-open',
            'origin_context' => 'platform-revenue-recovery',
        ], 'central');
    }
}
