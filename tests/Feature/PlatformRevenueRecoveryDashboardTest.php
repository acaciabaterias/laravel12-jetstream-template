<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformRevenueRecoveryDashboard;
use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformRevenueRecoveryDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_billing_operator_can_view_the_revenue_recovery_dashboard(): void
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
        $policy = PoliticaRecuperacaoReceita::factory()->create();
        CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
        ]);

        $response = $this
            ->actingAs($operador, 'platform')
            ->get(route('admin.recovery.index'));

        $response
            ->assertOk()
            ->assertSee('Saúde da recuperação de receita')
            ->assertSee($cliente->razao_social)
            ->assertSeeLivewire(PlatformRevenueRecoveryDashboard::class);
    }

    public function test_support_user_cannot_render_the_revenue_recovery_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $this->actingAs($support, 'platform');

        Livewire::test(PlatformRevenueRecoveryDashboard::class)
            ->assertForbidden();
    }
}
