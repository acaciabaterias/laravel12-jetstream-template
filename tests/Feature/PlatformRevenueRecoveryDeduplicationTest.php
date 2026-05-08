<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\CobrancaSaaSExterna;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Services\Billing\RevenueRecoveryCaseService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformRevenueRecoveryDeduplicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_revenue_recovery.events.publish_to_backbone', false);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
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

    public function test_failed_payment_signal_reuses_existing_case_without_duplicate_action(): void
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
            'status' => 'pending',
            'vencimento' => now()->subDay()->toDateString(),
        ]);
        $gateway = GatewayCobrancaSaaS::factory()->create();
        $cobranca = CobrancaSaaSExterna::factory()->create([
            'fatura_saas_id' => $fatura->id,
            'gateway_cobranca_saas_id' => $gateway->id,
            'status' => 'failed',
            'failure_reason' => 'gateway_timeout',
        ]);
        PoliticaRecuperacaoReceita::factory()->create([
            'stage_definitions' => [
                ['name' => 'd1', 'channel' => 'email', 'delay_hours' => 0],
            ],
        ]);

        $service = app(RevenueRecoveryCaseService::class);

        $initialCase = $service->openForInvoice($fatura, 'invoice_overdue');
        $reusedCase = $service->openFromPaymentFailure($cobranca);

        $this->assertSame($initialCase->id, $reusedCase->id);
        $this->assertSame(1, $initialCase->acoes()->count());
        $this->assertDatabaseCount('casos_recuperacao_receita', 1, 'central');
        $this->assertDatabaseCount('acoes_recuperacao_receita', 1, 'central');
    }
}
