<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Http\Middleware\TenantConnectionMiddleware;
use App\Jobs\ProcessXmlImportJob;
use App\Livewire\ContaSucataDashboard;
use App\Livewire\EstoqueAdjustmentForm;
use App\Livewire\EstoqueDashboard;
use App\Livewire\XmlImportForm;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\ContaSucataMovimentacao;
use App\Models\Deposito;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\User;
use App\Models\Vale;
use App\Models\XmlImportacao;
use App\Services\EstoqueSaldoService;
use App\Services\XmlNfeParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryReverseLogisticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PrometheusMetrics::class);

        Route::middleware('web')->get('/inventory/tenant-probe', function (Request $request) {
            return response()->json([
                'tenant_host' => config('database.connections.tenant.host'),
                'tenant_password' => config('database.connections.tenant.password'),
                'cliente_id' => optional($request->attributes->get('cliente'))->id,
            ]);
        });
    }

    public function test_stock_movements_update_consolidated_balance(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'INV-001',
            'marca' => 'Moura',
        ]);

        $deposito = Deposito::create([
            'nome' => 'Principal',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        $service = app(EstoqueSaldoService::class);
        $service->registrarMovimentacao($bateria, $deposito, 10, 'entrada', $user, 'compra_xml');
        $service->registrarMovimentacao($bateria, $deposito, 3, 'saida', $user, 'os');

        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'deposito_id' => $deposito->id,
            'quantidade_atual' => 7,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'estoque_movimentacoes',
            'action' => 'created',
        ]);
    }

    public function test_negative_stock_is_blocked(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'INV-002',
            'marca' => 'Heliar',
        ]);

        $deposito = Deposito::create([
            'nome' => 'Tecnico',
            'tipo' => 'tecnico',
            'status' => 'ativo',
        ]);

        $this->expectException(ValidationException::class);

        app(EstoqueSaldoService::class)
            ->registrarMovimentacao($bateria, $deposito, 1, 'saida', $user, 'ajuste_manual');
    }

    public function test_manual_adjustment_requires_justification(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'INV-002B',
            'marca' => 'Heliar',
        ]);

        $deposito = Deposito::create([
            'nome' => 'Ajustes',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        Livewire::test(EstoqueAdjustmentForm::class)
            ->set('bateriaId', $bateria->id)
            ->set('depositoId', $deposito->id)
            ->set('tipoOperacao', 'ajuste_positivo')
            ->set('quantidade', 1)
            ->set('origem', 'ajuste_manual')
            ->set('justificativa', '')
            ->call('salvar')
            ->assertHasErrors(['justificativa' => 'required_if']);
    }

    public function test_stock_transfer_between_deposits_updates_both_balances(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);

        $bateria = Bateria::create([
            'sku' => 'INV-TRF-01',
            'marca' => 'Moura',
        ]);

        $origem = Deposito::create([
            'nome' => 'Depósito A',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        $destino = Deposito::create([
            'nome' => 'Depósito B',
            'tipo' => 'tecnico',
            'status' => 'ativo',
        ]);

        $service = app(EstoqueSaldoService::class);
        $service->registrarMovimentacao($bateria, $origem, 10, 'entrada', $user, 'compra_xml');
        $service->transferirEntreDepositos($bateria, $origem, $destino, 4, $user, 'Balanceamento interno');

        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'deposito_id' => $origem->id,
            'quantidade_atual' => 6,
        ]);

        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'deposito_id' => $destino->id,
            'quantidade_atual' => 4,
        ]);

        $this->assertDatabaseCount('audit_logs', 6);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'estoque_movimentacoes',
            'action' => 'created',
        ]);
    }

    public function test_xml_import_form_blocks_duplicate_invoice_key(): void
    {
        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        XmlImportacao::create([
            'chave_nfe' => str_repeat('1', 44),
            'status' => 'processado',
            'payload_xml' => ['raw' => '<nfe />'],
        ]);

        Livewire::test(XmlImportForm::class)
            ->set('chaveNfe', str_repeat('1', 44))
            ->set('payloadXml', '<nfe>duplicada</nfe>')
            ->call('importar')
            ->assertHasErrors(['chaveNfe' => 'unique']);
    }

    public function test_xml_import_form_dispatches_processing_job_with_pending_status(): void
    {
        Queue::fake();

        $user = User::factory()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        Livewire::test(XmlImportForm::class)
            ->set('chaveNfe', str_repeat('4', 44))
            ->set('payloadXml', '<nfeProc><NFe><infNFe Id="NFe'.str_repeat('4', 44).'"></infNFe></NFe></nfeProc>')
            ->call('importar')
            ->assertHasNoErrors();

        $importacao = XmlImportacao::query()->where('chave_nfe', str_repeat('4', 44))->first();

        $this->assertNotNull($importacao);
        $this->assertSame('pendente', $importacao->status);
        Queue::assertPushed(ProcessXmlImportJob::class, function (ProcessXmlImportJob $job) use ($importacao): bool {
            return $job->xmlImportacaoId === $importacao->id;
        });
    }

    public function test_xml_import_job_processes_mapped_items_and_updates_stock(): void
    {
        $bateria = Bateria::create([
            'sku' => 'MAP-001',
            'marca' => 'Moura',
        ]);

        $importacao = XmlImportacao::create([
            'chave_nfe' => str_repeat('2', 44),
            'status' => 'pendente',
            'payload_xml' => [
                'raw' => '<nfeProc><NFe><infNFe Id="NFe'.str_repeat('2', 44).'"><det nItem="1"><prod><cProd>MAP-001</cProd><qCom>3</qCom></prod></det></infNFe></NFe></nfeProc>',
            ],
        ]);

        (new ProcessXmlImportJob($importacao->id))->handle(
            app(XmlNfeParser::class),
            app(EstoqueSaldoService::class),
        );

        $this->assertDatabaseHas('xml_importacoes', [
            'id' => $importacao->id,
            'status' => 'processado',
        ]);

        $this->assertDatabaseHas('estoque_saldos', [
            'bateria_id' => $bateria->id,
            'quantidade_atual' => 3,
        ]);
    }

    public function test_xml_import_job_marks_pending_when_item_is_not_mapped(): void
    {
        $importacao = XmlImportacao::create([
            'chave_nfe' => str_repeat('3', 44),
            'status' => 'pendente',
            'payload_xml' => [
                'raw' => '<nfeProc><NFe><infNFe Id="NFe'.str_repeat('3', 44).'"><det nItem="1"><prod><cProd>SKU-NAO-MAPEADO</cProd><qCom>2</qCom></prod></det></infNFe></NFe></nfeProc>',
            ],
        ]);

        (new ProcessXmlImportJob($importacao->id))->handle(
            app(XmlNfeParser::class),
            app(EstoqueSaldoService::class),
        );

        $importacao->refresh();

        $this->assertSame('pendente', $importacao->status);
        $this->assertStringContainsString('SKU-NAO-MAPEADO', (string) $importacao->log_erros);
    }

    public function test_scrap_account_keeps_running_balance(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::create([
            'sku' => 'INV-003',
            'marca' => 'Bosch',
        ]);

        Livewire::test(ContaSucataDashboard::class)
            ->set('bateriaId', $bateria->id)
            ->set('tipoMovimento', 'credito')
            ->set('quantidadeKg', 2.5)
            ->set('valorUnitario', 4.20)
            ->set('origem', 'retorno_cliente')
            ->call('registrarMovimento');

        $movimentacao = ContaSucataMovimentacao::query()->latest('id')->first();

        $this->assertNotNull($movimentacao);
        $this->assertSame('10.50', $movimentacao->saldo_resultante);
    }

    public function test_scrap_account_accepts_cliente_and_fornecedor_entities(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($user);

        $clienteId = Cliente::factory()->create()->id;

        Livewire::test(ContaSucataDashboard::class)
            ->set('entidadeTipo', 'cliente')
            ->set('entidadeId', $clienteId)
            ->set('tipoMovimento', 'credito')
            ->set('quantidadeKg', 1)
            ->set('valorUnitario', 5)
            ->set('origem', 'retorno_cliente')
            ->call('registrarMovimento')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('conta_sucata_movimentacoes', [
            'entidade_tipo' => 'tenant_cliente',
            'entidade_id' => $clienteId,
        ]);

        if (Schema::hasTable('fornecedores')) {
            $fornecedor = Fornecedor::query()->create([
                'nome' => 'Fornecedor Sucata',
                'documento' => '00999999000199',
                'ativo' => true,
            ]);

            Livewire::test(ContaSucataDashboard::class)
                ->set('entidadeTipo', 'fornecedor')
                ->set('entidadeId', $fornecedor->id)
                ->set('tipoMovimento', 'debito')
                ->set('quantidadeKg', 1)
                ->set('valorUnitario', 2)
                ->set('origem', 'acerto_fornecedor')
                ->call('registrarMovimento')
                ->assertHasNoErrors();

            $this->assertDatabaseHas('conta_sucata_movimentacoes', [
                'entidade_tipo' => Fornecedor::class,
                'entidade_id' => $fornecedor->id,
            ]);
        }
    }

    public function test_inventory_dashboard_route_renders_components_for_stock_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'estoquista',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSeeLivewire('estoque-dashboard')
            ->assertSeeLivewire('estoque-adjustment-form')
            ->assertSeeLivewire('xml-import-form')
            ->assertSeeLivewire('conta-sucata-dashboard');
    }

    public function test_estoque_dashboard_renders_cards_chart_and_top_sellers(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::query()->create([
            'sku' => 'INV-004',
            'marca' => 'Moura',
            'preco_venda' => 450,
        ]);

        $deposito = Deposito::query()->create([
            'nome' => 'Loja 01',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        app(EstoqueSaldoService::class)->registrarMovimentacao($bateria, $deposito, 12, 'entrada', $user, 'compra_xml');
        app(EstoqueSaldoService::class)->registrarMovimentacao($bateria, $deposito, 3, 'saida', $user, 'pedido_venda');

        $cliente = Cliente::factory()->create();
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'created_by' => $user->id,
        ]);

        $vale->itens()->create([
            'bateria_id' => $bateria->id,
            'quantidade' => 3,
            'preco_unitario_original' => 450,
            'preco_unitario_final' => 450,
            'flag_devolveu_sucata' => true,
        ]);

        Livewire::test(EstoqueDashboard::class)
            ->assertSee('Itens com saldo igual ou abaixo de 5.')
            ->assertSee('Soma dos saldos disponíveis.')
            ->assertSee('Produtos mais vendidos')
            ->assertSee('Alertas de shelf life')
            ->assertSee('Saídas por período')
            ->assertSee('INV-004')
            ->assertSee('Loja 01');
    }

    public function test_estoque_dashboard_shows_shelf_life_alert_when_item_exceeds_limit(): void
    {
        config()->set('inventory.shelf_life_days', 30);

        $user = User::factory()->withPersonalTeam()->create(['papel' => 'estoquista', 'ativo' => true]);
        $this->actingAs($user);

        $bateria = Bateria::query()->create([
            'sku' => 'INV-SHELF-01',
            'marca' => 'Heliar',
            'preco_venda' => 300,
        ]);
        $deposito = Deposito::query()->create([
            'nome' => 'Loja Shelf',
            'tipo' => 'principal',
            'status' => 'ativo',
        ]);

        app(EstoqueSaldoService::class)->registrarMovimentacao($bateria, $deposito, 5, 'entrada', $user, 'compra_xml');
        DB::table('estoque_movimentacoes')
            ->where('bateria_id', $bateria->id)
            ->where('deposito_id', $deposito->id)
            ->where('tipo_operacao', 'entrada')
            ->update(['data_movimentacao' => now()->subDays(45)]);

        Livewire::test(EstoqueDashboard::class)
            ->assertSee('Alertas de shelf life')
            ->assertSee('INV-SHELF-01')
            ->assertSee('Loja Shelf');
    }

    public function test_inventory_operations_are_isolated_between_tenants_without_cross_access(): void
    {
        $this->withoutMiddleware(PrometheusMetrics::class);

        $tenantA = Cliente::factory()->create([
            'subdominio' => 'tenant-a',
            'status' => 'active',
            'supabase_db_host' => 'db-tenant-a.supabase.co',
            'supabase_db_password' => 'pwd-tenant-a',
        ]);

        $tenantB = Cliente::factory()->create([
            'subdominio' => 'tenant-b',
            'status' => 'active',
            'supabase_db_host' => 'db-tenant-b.supabase.co',
            'supabase_db_password' => 'pwd-tenant-b',
        ]);

        $tenantUsesSharedPgBaseline = config('database.connections.tenant.driver') === 'pgsql'
            && filled((string) config('database.connections.tenant.host'));

        $responseA = $this->get('http://tenant-a.erp.com/inventory/tenant-probe');
        $responseB = $this->get('http://tenant-b.erp.com/inventory/tenant-probe');

        $responseA->assertOk()
            ->assertJson([
                'tenant_host' => $tenantUsesSharedPgBaseline
                    ? config('database.connections.tenant.host')
                    : 'db-tenant-a.supabase.co',
                'tenant_password' => $tenantUsesSharedPgBaseline
                    ? config('database.connections.tenant.password')
                    : 'pwd-tenant-a',
                'cliente_id' => $tenantA->id,
            ]);

        $responseB->assertOk()
            ->assertJson([
                'tenant_host' => $tenantUsesSharedPgBaseline
                    ? config('database.connections.tenant.host')
                    : 'db-tenant-b.supabase.co',
                'tenant_password' => $tenantUsesSharedPgBaseline
                    ? config('database.connections.tenant.password')
                    : 'pwd-tenant-b',
                'cliente_id' => $tenantB->id,
            ]);
    }
}
