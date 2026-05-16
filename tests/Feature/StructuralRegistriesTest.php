<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Livewire\AplicacaoCloner;
use App\Livewire\ApplicationManager;
use App\Livewire\BateriaManager;
use App\Models\Aplicacao;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class StructuralRegistriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/structural/tenant-probe', function (Request $request) {
            return response()->json([
                'tenant_host' => config('database.connections.tenant.host'),
                'cliente_id' => optional($request->attributes->get('cliente'))->id,
            ]);
        });
    }

    public function test_fabricante_crud_records_audit_logs(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create([
            'nome' => 'Moura',
            'codigo' => 'MOU',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'created',
            'table_name' => 'fabricantes',
            'record_id' => $fabricante->id,
        ]);

        $fabricante->update(['codigo' => 'MOU-01']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated',
            'record_id' => $fabricante->id,
        ]);

        $fabricante->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'record_id' => $fabricante->id,
        ]);
    }

    public function test_structural_crud_supports_json_attributes_and_reverse_logistics(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'VW']);

        $veiculoOrigem = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Gol',
            'atributos_dinamicos' => ['categoria' => 'Carro'],
        ]);

        $bateria = Bateria::create([
            'sku' => 'BAT-01',
            'marca' => 'Zetta',
            'peso_sucata_kg' => 12.50,
            'valor_base_sucata_kg' => 4.20,
            'atributos_dinamicos' => ['tipo' => 'AGM'],
        ]);

        $this->assertSame('Carro', $veiculoOrigem->atributos_dinamicos['categoria']);
        $this->assertSame('AGM', $bateria->atributos_dinamicos['tipo']);
        $this->assertSame('12.50', $bateria->peso_sucata_kg);
        $this->assertSame('4.20', $bateria->valor_base_sucata_kg);
    }

    public function test_cloning_applications_does_not_duplicate_existing_ones(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'VW']);

        $veiculoOrigem = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Gol',
        ]);

        $veiculoDestino = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Fox',
        ]);

        $bateria = Bateria::create([
            'sku' => 'BAT-01',
            'marca' => 'Zetta',
        ]);

        $veiculoOrigem->baterias()->attach($bateria->id, ['observacao' => 'Aplicacao original']);

        Livewire::test(AplicacaoCloner::class)
            ->call('openCloner', $veiculoDestino->id)
            ->set('origemVeiculoId', $veiculoOrigem->id)
            ->call('cloneAplicacoes');

        $this->assertCount(1, $veiculoDestino->fresh()->baterias);

        Livewire::test(AplicacaoCloner::class)
            ->call('openCloner', $veiculoDestino->id)
            ->set('origemVeiculoId', $veiculoOrigem->id)
            ->call('cloneAplicacoes');

        $this->assertCount(1, $veiculoDestino->fresh()->baterias);
    }

    public function test_combined_vehicle_search_filters_by_fabricante_modelo_e_ano(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricanteA = Fabricante::create(['nome' => 'VW']);
        $fabricanteB = Fabricante::create(['nome' => 'GM']);

        Veiculo::create([
            'fabricante_id' => $fabricanteA->id,
            'modelo' => 'Gol G4',
            'ano_inicio' => 2020,
        ]);

        Veiculo::create([
            'fabricante_id' => $fabricanteB->id,
            'modelo' => 'Onix',
            'ano_inicio' => 2020,
        ]);

        $filtered = Veiculo::query()
            ->where('fabricante_id', $fabricanteA->id)
            ->where('modelo', 'like', '%Gol%')
            ->where('ano_inicio', 2020)
            ->pluck('modelo')
            ->all();

        $this->assertSame(['Gol G4'], $filtered);
    }

    public function test_reverse_vehicle_lookup_by_battery_is_available_in_vehicle_editor(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'VW']);
        $veiculo = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Fox',
        ]);
        $bateria = Bateria::create([
            'sku' => 'BAT-REV-01',
            'marca' => 'Moura',
        ]);

        Aplicacao::create([
            'veiculo_id' => $veiculo->id,
            'bateria_id' => $bateria->id,
            'observacao' => 'Aplicacao reversa',
        ]);

        $skusRelacionados = $veiculo->fresh('baterias')->baterias->pluck('sku')->all();

        $this->assertContains('BAT-REV-01', $skusRelacionados);
    }

    public function test_structural_operations_are_isolated_between_tenants_without_cross_access(): void
    {
        $this->withoutMiddleware(PrometheusMetrics::class);

        $tenantA = Cliente::factory()->create([
            'subdominio' => 'struct-a',
            'status' => 'active',
            'supabase_db_host' => 'db-struct-a.supabase.co',
        ]);
        $tenantB = Cliente::factory()->create([
            'subdominio' => 'struct-b',
            'status' => 'active',
            'supabase_db_host' => 'db-struct-b.supabase.co',
        ]);

        $tenantUsesSharedPgBaseline = config('database.connections.tenant.driver') === 'pgsql'
            && filled((string) config('database.connections.tenant.host'));

        $responseA = $this->get('http://struct-a.erp.com/structural/tenant-probe');
        $responseB = $this->get('http://struct-b.erp.com/structural/tenant-probe');

        $responseA->assertOk()->assertJson([
            'tenant_host' => $tenantUsesSharedPgBaseline
                ? config('database.connections.tenant.host')
                : 'db-struct-a.supabase.co',
            'cliente_id' => $tenantA->id,
        ]);
        $responseB->assertOk()->assertJson([
            'tenant_host' => $tenantUsesSharedPgBaseline
                ? config('database.connections.tenant.host')
                : 'db-struct-b.supabase.co',
            'cliente_id' => $tenantB->id,
        ]);
    }

    public function test_soft_delete_control_keeps_application_relationships_for_linked_records(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'Fiat']);
        $veiculo = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Argo',
        ]);
        $bateria = Bateria::create([
            'sku' => 'BAT-LINK-01',
            'marca' => 'Heliar',
            'preco_venda' => 0,
        ]);

        Aplicacao::create([
            'veiculo_id' => $veiculo->id,
            'bateria_id' => $bateria->id,
            'observacao' => 'Vinculo existente',
        ]);

        $bateria->delete();

        $this->assertSoftDeleted('baterias', ['id' => $bateria->id]);
        $this->assertDatabaseHas('aplicacoes', [
            'veiculo_id' => $veiculo->id,
            'bateria_id' => $bateria->id,
            'deleted_at' => null,
        ]);
    }

    public function test_soft_delete_control_keeps_vehicle_when_fabricante_has_links(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'Renault']);
        $veiculo = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Kwid',
        ]);

        $fabricante->delete();

        $this->assertSoftDeleted('fabricantes', ['id' => $fabricante->id]);
        $this->assertDatabaseHas('veiculos', [
            'id' => $veiculo->id,
            'deleted_at' => null,
        ]);
    }

    public function test_soft_delete_control_keeps_application_when_veiculo_has_links(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'Peugeot']);
        $veiculo = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => '208',
        ]);
        $bateria = Bateria::create([
            'sku' => 'BAT-LINK-02',
            'marca' => 'Moura',
            'preco_venda' => 0,
        ]);

        Aplicacao::create([
            'veiculo_id' => $veiculo->id,
            'bateria_id' => $bateria->id,
            'observacao' => 'Vinculo veiculo',
        ]);

        $veiculo->delete();

        $this->assertSoftDeleted('veiculos', ['id' => $veiculo->id]);
        $this->assertDatabaseHas('aplicacoes', [
            'veiculo_id' => $veiculo->id,
            'bateria_id' => $bateria->id,
            'deleted_at' => null,
        ]);
    }

    public function test_battery_sku_must_be_unique_inside_tenant(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        Bateria::create([
            'sku' => 'SKU-UNICO-01',
            'marca' => 'Moura',
            'preco_venda' => 100,
        ]);

        Livewire::test(BateriaManager::class)
            ->set('sku', 'SKU-UNICO-01')
            ->set('marca', 'Heliar')
            ->set('preco_venda', 90)
            ->call('store')
            ->assertHasErrors(['sku']);
    }

    public function test_application_manager_component_adds_and_removes_vehicle_battery_links(): void
    {
        $user = User::factory()->create(['papel' => 'gestor']);
        $this->actingAs($user);

        $fabricante = Fabricante::create(['nome' => 'Nissan']);
        $veiculo = Veiculo::create([
            'fabricante_id' => $fabricante->id,
            'modelo' => 'Versa',
        ]);
        $bateria = Bateria::create([
            'sku' => 'BAT-APP-003',
            'marca' => 'Heliar',
            'preco_venda' => 0,
        ]);

        Livewire::test(ApplicationManager::class, ['vehicleId' => $veiculo->id])
            ->set('bateriaSelecionadaId', $bateria->id)
            ->set('observacao', 'Aplicacao via aba dedicada')
            ->call('addAplicacao')
            ->assertHasNoErrors();

        $aplicacaoId = Aplicacao::query()
            ->where('veiculo_id', $veiculo->id)
            ->where('bateria_id', $bateria->id)
            ->value('id');

        $this->assertNotNull($aplicacaoId);

        Livewire::test(ApplicationManager::class, ['vehicleId' => $veiculo->id])
            ->call('removeAplicacao', $aplicacaoId)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('aplicacoes', ['id' => $aplicacaoId]);
    }
}
