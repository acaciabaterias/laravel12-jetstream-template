<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformPaymentsDashboard;
use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\CobrancaSaaSExterna;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Models\PlanoComercial;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformPaymentsDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_billing_operator_can_view_the_platform_payments_dashboard(): void
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
        ]);
        $gateway = GatewayCobrancaSaaS::factory()->create();
        CobrancaSaaSExterna::factory()->create([
            'fatura_saas_id' => $fatura->id,
            'gateway_cobranca_saas_id' => $gateway->id,
            'external_reference' => 'saas-dashboard-1',
        ]);

        $response = $this
            ->actingAs($operador, 'platform')
            ->get(route('admin.payments.index'));

        $response
            ->assertOk()
            ->assertSee('Saude de cobrancas e conciliacao')
            ->assertSee($cliente->razao_social)
            ->assertSeeLivewire(PlatformPaymentsDashboard::class);
    }

    public function test_support_user_cannot_render_the_platform_payments_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $this->actingAs($support, 'platform');

        Livewire::test(PlatformPaymentsDashboard::class)
            ->assertForbidden();
    }
}
