<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformBillingDashboard;
use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use App\Services\Billing\SaasInvoiceService;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformBillingPortfolioFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runCentralBillingMigrations();
    }

    public function test_dashboard_filters_the_portfolio_by_status_and_risk(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($operador, 'platform');

        $planoEssential = PlanoComercial::factory()->create([
            'nome' => 'Plano Essential',
            'slug' => 'essential',
        ]);
        $planoEnterprise = PlanoComercial::factory()->create([
            'nome' => 'Plano Enterprise',
            'slug' => 'enterprise',
        ]);
        $politica = PoliticaInadimplencia::factory()->create([
            'grace_period_days' => 2,
            'block_after_days' => 5,
        ]);

        $clienteAtivo = Cliente::factory()->create([
            'razao_social' => 'Cliente Ativo',
            'status' => 'active',
        ]);
        $clienteBloqueado = Cliente::factory()->create([
            'razao_social' => 'Cliente Bloqueado',
            'status' => 'active',
        ]);

        $assinaturaAtiva = app(SubscriptionLifecycleService::class)->activate(
            cliente: $clienteAtivo,
            planoComercial: $planoEssential,
            politicaInadimplencia: $politica,
        );
        $assinaturaBloqueada = app(SubscriptionLifecycleService::class)->activate(
            cliente: $clienteBloqueado,
            planoComercial: $planoEnterprise,
            politicaInadimplencia: $politica,
        );

        app(SaasInvoiceService::class)->createInvoice($assinaturaBloqueada, [
            'vencimento' => Carbon::now()->subDays(8)->toDateString(),
            'valor' => 799.90,
        ]);
        app(DelinquencyPolicyEvaluator::class)->assess($assinaturaBloqueada, Carbon::now());

        Livewire::test(PlatformBillingDashboard::class)
            ->set('statusFilter', 'blocked')
            ->assertSee('Cliente Bloqueado')
            ->assertDontSee('Cliente Ativo')
            ->set('statusFilter', 'all')
            ->set('riskFilter', 'blocked')
            ->assertSee('Cliente Bloqueado')
            ->assertDontSee('Cliente Ativo');
    }

    private function runCentralBillingMigrations(): void
    {
        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }
}
