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
use App\Models\UsuarioPlataforma;
use App\Services\Billing\RevenueRecoveryEscalationService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformRevenueRecoveryEscalationTest extends TestCase
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

    public function test_it_automatically_escalates_a_critical_case(): void
    {
        $cliente = Cliente::factory()->create(['subdominio' => 'tenant-escalation']);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'vencimento' => now()->subDays(10)->toDateString(),
        ]);
        $owner = UsuarioPlataforma::factory()->billing()->create();
        $policy = PoliticaRecuperacaoReceita::factory()->create();
        $case = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'severity' => 'high',
        ]);
        AcaoRecuperacaoReceita::factory()->count(2)->create([
            'caso_recuperacao_receita_id' => $case->id,
            'status' => 'failed',
        ]);

        $escalated = app(RevenueRecoveryEscalationService::class)->escalate($case, $owner, $owner, 'Caso reincidente.');

        $this->assertSame('escalated', $escalated->status->value);
        $this->assertSame($owner->id, $escalated->owner_user_id);
        $this->assertDatabaseHas('acoes_recuperacao_receita', [
            'caso_recuperacao_receita_id' => $case->id,
            'action_type' => 'escalation',
            'result_code' => 'escalated',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'CASO_RECUPERACAO_ESCALADO',
            'tenant_external_ref' => 'tenant-escalation',
        ], 'central');
    }
}
