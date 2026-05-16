<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformSubscriptionManager;
use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformBillingSubscriptionManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_billing_operator_can_activate_subscription_from_livewire_manager(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $cliente = Cliente::factory()->create(['status' => 'trial']);
        $plano = PlanoComercial::factory()->create(['slug' => 'pro']);
        $politica = PoliticaInadimplencia::factory()->create(['nome' => 'Politica Base']);

        $this->actingAs($operador, 'platform');

        Livewire::test(PlatformSubscriptionManager::class)
            ->set('clienteId', (string) $cliente->id)
            ->set('planoId', (string) $plano->id)
            ->set('politicaInadimplenciaId', (string) $politica->id)
            ->set('status', 'active')
            ->set('reason', 'Ativacao comercial.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assinaturas', [
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'politica_inadimplencia_id' => $politica->id,
            'status' => 'active',
        ], 'central');
    }
}
