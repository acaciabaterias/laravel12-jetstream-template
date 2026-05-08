<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformRevenueRecoveryFiltersTest extends TestCase
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

    public function test_recovery_inspection_endpoint_filters_by_stage_and_owner(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $outroOperador = UsuarioPlataforma::factory()->billing()->create();
        $cliente = Cliente::factory()->create([
            'razao_social' => 'Cliente Recuperacao',
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

        CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'current_stage' => 'd3',
            'severity' => 'critical',
            'owner_user_id' => $operador->id,
        ]);

        CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'current_stage' => 'd1',
            'severity' => 'low',
            'owner_user_id' => $outroOperador->id,
            'status' => 'paused',
        ]);

        $response = $this->actingAs($operador, 'platform')
            ->getJson(route('admin.recovery.inspection', [
                'stage' => 'd3',
                'owner' => $operador->id,
            ]));

        $response->assertOk()
            ->assertJsonPath('summary.open_cases', 1)
            ->assertJsonPath('cases.0.tenant_name', 'Cliente Recuperacao')
            ->assertJsonPath('cases.0.stage_name', 'd3')
            ->assertJsonPath('cases.0.owner', $operador->name);
    }
}
