<?php

namespace Tests\Feature;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\ItemVale;
use App\Models\User;
use App\Models\Vale;
use App\Models\Deposito;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TraceabilityTest extends TestCase
{
    use RefreshDatabase;

    private Filial $filial;
    private User $vendedor;
    private Cliente $cliente;
    private Bateria $bateria;
    private Deposito $deposito;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filial = Filial::factory()->create();
        $this->vendedor = User::factory()->create(['filial_id' => $this->filial->id]);
        
        $this->deposito = Deposito::create([
            'nome' => 'Depósito Teste',
            'filial_id' => $this->filial->id,
            'is_principal' => true
        ]);

        $this->cliente = Cliente::create([
            'cnpj' => '12.345.678/0001-00',
            'razao_social' => 'Traceability Client',
            'nome_fantasia' => 'Trace Client',
            'email_contato' => 'trace@test.com',
            'telefone' => '1188888888',
            'subdominio' => 'trace-test-' . uniqid(),
            'supabase_project_ref' => 'ref-trace-' . uniqid(),
            'supabase_url' => 'http://test.co',
            'supabase_db_host' => 'host',
            'supabase_db_password' => 'pass',
            'supabase_anon_key' => 'key',
            'supabase_service_role_key' => 'key',
        ]);

        $this->bateria = Bateria::create([
            'sku' => 'BAT-TRACE-001',
            'marca' => 'Heliar',
            'amperagem' => 60,
            'polo' => 'D',
            'preco_venda' => 500.00,
            'filial_id' => $this->filial->id,
        ]);

        $this->actingAs($this->vendedor);
    }

    public function test_pdv_can_update_serial_number_on_item()
    {
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberto',
        ]);

        $item = ItemVale::create([
            'vale_id' => $vale->id,
            'bateria_id' => $this->bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 500.00,
            'preco_unitario_final' => 500.00,
        ]);

        Livewire::test('pdv-manager', ['valeId' => $vale->id])
            ->call('updateSerialNumber', $item->id, 'SN-123456');

        $this->assertDatabaseHas('item_vales', [
            'id' => $item->id,
            'numero_serie' => 'SN-123456',
        ]);
    }

    public function test_suporte_central_finds_customer_by_serial_number()
    {
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'faturado',
        ]);

        ItemVale::create([
            'vale_id' => $vale->id,
            'bateria_id' => $this->bateria->id,
            'quantidade' => 1,
            'preco_unitario_original' => 500.00,
            'preco_unitario_final' => 500.00,
            'numero_serie' => 'SN-TRACE-999'
        ]);

        Livewire::test('suporte-central')
            ->set('search', 'SN-TRACE-999')
            ->call('search')
            ->assertSee($this->cliente->razao_social)
            ->assertSee('Heliar');
    }

    public function test_suporte_central_shows_client_timeline()
    {
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'faturado',
        ]);

        Livewire::test('suporte-central')
            ->set('search', 'Traceability')
            ->call('search')
            ->assertSee("Compra de Bateria")
            ->assertSee($this->cliente->razao_social);
    }
}
