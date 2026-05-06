<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Http\Middleware\TenantConnectionMiddleware;
use App\Jobs\ConvertValeToOsJob;
use App\Jobs\ConvertValeToPedidoJob;
use App\Livewire\ValeForm;
use App\Livewire\ValeList;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Deposito;
use App\Models\EstoqueSaldo;
use App\Models\Filial;
use App\Models\ReservaEstoque;
use App\Models\User;
use App\Models\Vale;
use App\Services\EstoqueSaldoService;
use App\Services\ReservaEstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class SalesServiceOsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PrometheusMetrics::class);

        Route::middleware('web')->get('/sales/tenant-probe', function (Request $request) {
            return response()->json([
                'tenant_host' => config('database.connections.tenant.host'),
                'cliente_id' => optional($request->attributes->get('cliente'))->id,
            ]);
        });
    }

    protected function seedInventory(Bateria $bateria, int $quantidade = 5): Deposito
    {
        $deposito = Deposito::query()->create([
            'nome' => 'Deposito Vendas',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        EstoqueSaldo::query()->create([
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade_atual' => $quantidade,
        ]);

        return $deposito;
    }

    public function test_vendedor_creates_vale_with_scrap_price_and_reservation(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $cliente = Cliente::factory()->create();
        $bateria = Bateria::create([
            'sku' => 'SALE-001',
            'marca' => 'Moura',
            'preco_venda' => 500,
            'peso_sucata_kg' => 10,
            'valor_base_sucata_kg' => 2.50,
        ]);
        $this->seedInventory($bateria, 3);

        Livewire::test(ValeForm::class)
            ->set('clienteId', $cliente->id)
            ->set('observacoes', 'Venda balcão')
            ->call('createVale')
            ->set('bateriaId', $bateria->id)
            ->set('quantidade', 1)
            ->set('devolveuSucata', false)
            ->assertSet('previewPrecoOriginal', 500.0)
            ->assertSet('previewAcrescimoSucata', 25.0)
            ->assertSet('previewPrecoFinal', 525.0)
            ->assertSee('Preço base')
            ->assertSee('Acréscimo sucata')
            ->call('addItem')
            ->assertHasNoErrors();

        $vale = Vale::query()->with('itens')->firstOrFail();
        $item = $vale->itens->first();

        $this->assertSame('525.00', $item->preco_unitario_final);
        $this->assertDatabaseHas('reservas_estoque', [
            'vale_id' => $vale->id,
            'status' => 'reservada',
        ]);
    }

    public function test_reservation_blocks_when_stock_is_insufficient(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $cliente = Cliente::factory()->create();
        $bateria = Bateria::create([
            'sku' => 'SALE-002',
            'marca' => 'Heliar',
            'preco_venda' => 300,
        ]);
        $this->seedInventory($bateria, 0);

        Livewire::test(ValeForm::class)
            ->set('clienteId', $cliente->id)
            ->call('createVale')
            ->set('bateriaId', $bateria->id)
            ->set('quantidade', 1)
            ->call('addItem')
            ->assertHasErrors(['quantidade']);
    }

    public function test_converting_vale_to_pedido_confirms_stock_and_generates_scrap_debit(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $cliente = Cliente::factory()->create();
        $bateria = Bateria::create([
            'sku' => 'SALE-003',
            'marca' => 'Bosch',
            'preco_venda' => 400,
            'peso_sucata_kg' => 8,
            'valor_base_sucata_kg' => 3,
        ]);
        $deposito = $this->seedInventory($bateria, 4);

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Conversao para pedido',
            'created_by' => $user->id,
        ]);

        $item = $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 400,
            'preco_unitario_final' => 424,
            'flag_devolveu_sucata' => false,
        ]);

        ReservaEstoque::query()->create([
            'vale_id' => $vale->id,
            'item_vale_id' => $item->id,
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade' => 1,
            'status' => 'reservada',
        ]);

        (new ConvertValeToPedidoJob($vale->id, $user->id))->handle(app(ReservaEstoqueService::class), app(EstoqueSaldoService::class));

        $this->assertDatabaseHas('pedidos_venda', [
            'vale_id' => $vale->id,
            'status' => 'faturado',
        ]);
        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade_atual' => 3,
        ]);
        $this->assertDatabaseHas('conta_sucata_movimentacoes', [
            'entidade_tipo' => Cliente::class,
            'entidade_id' => $cliente->id,
            'tipo_movimento' => 'debito',
        ]);
    }

    public function test_converting_vale_to_service_order_creates_os(): void
    {
        $vendedor = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $tecnico = User::factory()->create(['papel' => 'tecnico', 'ativo' => true]);
        $cliente = Cliente::factory()->create();

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Encaminhar garantia',
            'created_by' => $vendedor->id,
        ]);

        (new ConvertValeToOsJob($vale->id, $tecnico->id))->handle();

        $this->assertDatabaseHas('ordens_servico', [
            'vale_id' => $vale->id,
            'tecnico_responsavel_id' => $tecnico->id,
            'status' => 'aberta',
        ]);
    }

    public function test_dashboard_renders_sales_components_for_sales_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'vendedor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSeeLivewire('vale-form')
            ->assertSeeLivewire('vale-list')
            ->assertSeeLivewire('vale-conversion-actions');
    }

    public function test_dashboard_hides_sales_components_for_non_sales_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'tecnico',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertDontSeeLivewire('vale-form')
            ->assertDontSeeLivewire('vale-list')
            ->assertDontSeeLivewire('vale-conversion-actions');
    }

    public function test_vale_form_filters_batteries_with_dynamic_search(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        Bateria::query()->create([
            'sku' => 'BAT-ALFA',
            'marca' => 'Moura',
            'preco_venda' => 500,
        ]);

        Bateria::query()->create([
            'sku' => 'BAT-BETA',
            'marca' => 'Heliar',
            'preco_venda' => 420,
        ]);

        $bateriaAlfaId = (int) Bateria::query()->where('sku', 'BAT-ALFA')->value('id');

        Livewire::test(ValeForm::class)
            ->set('buscaBateria', 'ALFA')
            ->assertSee('BAT-ALFA')
            ->assertDontSee('BAT-BETA')
            ->call('selectBateria', $bateriaAlfaId)
            ->set('buscaBateria', 'BETA')
            ->assertSet('bateriaId', $bateriaAlfaId)
            ->assertSee('BAT-ALFA · Moura');
    }

    public function test_vale_list_can_cancel_an_open_vale(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $cliente = Cliente::factory()->create();
        $bateria = Bateria::query()->create([
            'sku' => 'SALE-004',
            'marca' => 'Moura',
            'preco_venda' => 380,
        ]);
        $deposito = $this->seedInventory($bateria, 2);

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Cancelar atendimento',
            'created_by' => $user->id,
        ]);

        $item = $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 380,
            'preco_unitario_final' => 380,
            'flag_devolveu_sucata' => true,
        ]);

        ReservaEstoque::query()->create([
            'vale_id' => $vale->id,
            'item_vale_id' => $item->id,
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade' => 1,
            'status' => 'reservada',
        ]);

        Livewire::test(ValeList::class)
            ->call('cancelVale', $vale->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vales', [
            'id' => $vale->id,
            'status' => 'cancelado',
        ]);

        $this->assertDatabaseHas('reservas_estoque', [
            'vale_id' => $vale->id,
            'status' => 'estornada',
        ]);
    }

    public function test_vale_list_can_faturar_an_open_vale(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $cliente = Cliente::factory()->create();
        $bateria = Bateria::query()->create([
            'sku' => 'SALE-005',
            'marca' => 'Bosch',
            'preco_venda' => 430,
            'peso_sucata_kg' => 6,
            'valor_base_sucata_kg' => 2.5,
        ]);
        $deposito = $this->seedInventory($bateria, 3);

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Faturar atendimento',
            'created_by' => $user->id,
        ]);

        $item = $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 430,
            'preco_unitario_final' => 430,
            'flag_devolveu_sucata' => true,
        ]);

        ReservaEstoque::query()->create([
            'vale_id' => $vale->id,
            'item_vale_id' => $item->id,
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade' => 1,
            'status' => 'reservada',
        ]);

        Livewire::test(ValeList::class)
            ->call('faturarVale', $vale->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vales', [
            'id' => $vale->id,
            'status' => 'faturado',
        ]);

        $this->assertDatabaseHas('pedidos_venda', [
            'vale_id' => $vale->id,
            'status' => 'faturado',
        ]);
    }

    public function test_vale_list_mobile_view_uses_read_only_cache_without_breaking_filters(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $clienteAlfa = Cliente::factory()->create(['razao_social' => 'Cliente Alfa']);
        $clienteBeta = Cliente::factory()->create(['razao_social' => 'Cliente Beta']);

        Vale::query()->create([
            'cliente_id' => $clienteAlfa->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'created_by' => $user->id,
        ]);

        Vale::query()->create([
            'cliente_id' => $clienteBeta->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'created_by' => $user->id,
        ]);

        $this->withHeader('User-Agent', 'Mobile Test Agent');

        Livewire::test(ValeList::class)
            ->set('search', 'Alfa')
            ->assertSee('Cliente Alfa')
            ->assertDontSee('Cliente Beta');
    }

    public function test_sales_operations_are_isolated_between_tenants_without_cross_access(): void
    {
        $this->withoutMiddleware(PrometheusMetrics::class);

        $tenantA = Cliente::factory()->create([
            'subdominio' => 'sales-a',
            'status' => 'active',
            'supabase_db_host' => 'db-sales-a.supabase.co',
        ]);

        $tenantB = Cliente::factory()->create([
            'subdominio' => 'sales-b',
            'status' => 'active',
            'supabase_db_host' => 'db-sales-b.supabase.co',
        ]);

        $responseA = $this->get('http://sales-a.erp.com/sales/tenant-probe');
        $responseB = $this->get('http://sales-b.erp.com/sales/tenant-probe');

        $responseA->assertOk()->assertJson([
            'tenant_host' => 'db-sales-a.supabase.co',
            'cliente_id' => $tenantA->id,
        ]);

        $responseB->assertOk()->assertJson([
            'tenant_host' => 'db-sales-b.supabase.co',
            'cliente_id' => $tenantB->id,
        ]);
    }

    public function test_converting_vale_to_pedido_creates_scrap_account_entry_when_none_exists(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $cliente = Cliente::factory()->create();
        $bateria = Bateria::create([
            'sku' => 'SALE-006',
            'marca' => 'Bosch',
            'preco_venda' => 400,
            'peso_sucata_kg' => 8,
            'valor_base_sucata_kg' => 3,
        ]);
        $deposito = $this->seedInventory($bateria, 2);

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Cliente sem histórico de sucata',
            'created_by' => $user->id,
        ]);

        $item = $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 400,
            'preco_unitario_final' => 424,
            'flag_devolveu_sucata' => false,
        ]);

        ReservaEstoque::query()->create([
            'vale_id' => $vale->id,
            'item_vale_id' => $item->id,
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade' => 1,
            'status' => 'reservada',
        ]);

        $this->assertDatabaseCount('conta_sucata_movimentacoes', 0);

        (new ConvertValeToPedidoJob($vale->id, $user->id))->handle(app(ReservaEstoqueService::class), app(EstoqueSaldoService::class));

        $this->assertDatabaseHas('conta_sucata_movimentacoes', [
            'entidade_tipo' => Cliente::class,
            'entidade_id' => $cliente->id,
            'tipo_movimento' => 'debito',
            'origem' => 'vale_sem_sucata',
        ]);
    }

    public function test_critical_sales_operations_are_audited(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'vendedor', 'ativo' => true]);
        $this->actingAs($user);

        $cliente = Cliente::factory()->create();
        $bateria = Bateria::query()->create([
            'sku' => 'SALE-007',
            'marca' => 'Moura',
            'preco_venda' => 390,
        ]);
        $deposito = $this->seedInventory($bateria, 3);

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Auditoria crítica',
            'created_by' => $user->id,
        ]);

        $item = $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 390,
            'preco_unitario_final' => 390,
            'flag_devolveu_sucata' => true,
        ]);

        ReservaEstoque::query()->create([
            'vale_id' => $vale->id,
            'item_vale_id' => $item->id,
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade' => 1,
            'status' => 'reservada',
        ]);

        Livewire::test(ValeList::class)
            ->call('cancelVale', $vale->id)
            ->assertHasNoErrors();

        $valeFaturar = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'aberto',
            'data_criacao' => now(),
            'observacoes' => 'Auditoria faturamento',
            'created_by' => $user->id,
        ]);

        $itemFaturar = $valeFaturar->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 390,
            'preco_unitario_final' => 390,
            'flag_devolveu_sucata' => true,
        ]);

        ReservaEstoque::query()->create([
            'vale_id' => $valeFaturar->id,
            'item_vale_id' => $itemFaturar->id,
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade' => 1,
            'status' => 'reservada',
        ]);

        Livewire::test(ValeList::class)
            ->call('faturarVale', $valeFaturar->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'vales',
            'action' => 'updated',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'reservas_estoque',
            'action' => 'updated',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'pedidos_venda',
            'action' => 'created',
        ]);
    }
}
