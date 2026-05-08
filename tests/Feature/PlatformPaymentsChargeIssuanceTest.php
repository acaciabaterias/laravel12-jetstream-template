<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformPaymentsManager;
use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Models\PlanoComercial;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformPaymentsChargeIssuanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_payments.events.publish_to_backbone', false);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_billing_operator_can_emit_a_saas_charge_from_livewire_manager(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $cliente = Cliente::factory()->create();
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'referencia' => '2026-05',
            'status' => 'pending',
        ]);
        $gateway = GatewayCobrancaSaaS::factory()->create();

        $this->actingAs($operador, 'platform');

        Livewire::test(PlatformPaymentsManager::class)
            ->set('faturaSaaSId', (string) $fatura->id)
            ->set('gatewayCobrancaSaaSId', (string) $gateway->id)
            ->set('paymentChannel', 'boleto')
            ->set('reason', 'Primeira emissao.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cobrancas_saas_externas', [
            'fatura_saas_id' => $fatura->id,
            'gateway_cobranca_saas_id' => $gateway->id,
            'payment_channel' => 'boleto',
            'status' => 'submitted',
        ], 'central');
    }
}
