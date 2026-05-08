<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformBillingDashboard;
use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformBillingDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runCentralBillingMigrations();
    }

    public function test_billing_operator_can_view_the_platform_billing_dashboard(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $cliente = Cliente::factory()->create(['status' => 'active']);
        $plano = PlanoComercial::factory()->create(['nome' => 'Plano Pro']);

        app(SubscriptionLifecycleService::class)->activate(
            cliente: $cliente,
            planoComercial: $plano,
            actor: $operador,
        );

        $response = $this
            ->actingAs($operador, 'platform')
            ->get(route('admin.billing.index'));

        $response
            ->assertOk()
            ->assertSee('Saude comercial da base')
            ->assertSee($cliente->razao_social)
            ->assertSeeLivewire(PlatformBillingDashboard::class);
    }

    public function test_support_user_cannot_render_the_platform_billing_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $this->actingAs($support, 'platform');

        Livewire::test(PlatformBillingDashboard::class)
            ->assertForbidden();
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
