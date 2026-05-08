<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlanCatalogManager;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformBillingPlanCatalogManagerTest extends TestCase
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

    public function test_billing_operator_can_create_a_plan_from_livewire_manager(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($operador, 'platform');

        Livewire::test(PlanCatalogManager::class)
            ->set('nome', 'Plano Growth')
            ->set('slug', 'growth')
            ->set('precoMensal', '249.90')
            ->set('precoAnual', '2499.00')
            ->set('periodicidade', 'mensal')
            ->set('maxUsuarios', 12)
            ->set('maxEstoqueItens', 4000)
            ->set('hasWhiteLabel', true)
            ->set('hasSupportPriority', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('planos', [
            'slug' => 'growth',
            'nome' => 'Plano Growth',
        ], 'central');
    }
}
