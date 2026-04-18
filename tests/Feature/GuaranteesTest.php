<?php

namespace Tests\Feature;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\ItemVale;
use App\Models\OrdemServicoGarantia;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Jobs\RecalculateProductReturnIndexJob;
use Tests\TestCase;

class GuaranteesTest extends TestCase
{
    use RefreshDatabase;

    private Filial $filial;
    private User $vendedor;
    private Cliente $cliente;
    private Bateria $bateria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filial = Filial::factory()->create();
        $this->vendedor = User::factory()->create(['filial_id' => $this->filial->id]);
        
        $this->cliente = Cliente::create([
            'cnpj' => '12.345.678/0001-99',
            'razao_social' => 'Garantia Test Client',
            'nome_fantasia' => 'Test Gar',
            'email_contato' => 'gar@test.com',
            'telefone' => '1199999998',
            'subdominio' => 'gar-test-' . uniqid(),
            'supabase_project_ref' => 'ref-gar-' . uniqid(),
            'supabase_url' => 'http://test.co',
            'supabase_db_host' => 'host',
            'supabase_db_password' => 'pass',
            'supabase_anon_key' => 'key',
            'supabase_service_role_key' => 'key',
        ]);

        $this->bateria = Bateria::create([
            'sku' => 'BAT-TEST-001',
            'marca' => 'Moura',
            'amperagem' => 60,
            'polo' => 'D',
            'preco_venda' => 450.00,
            'filial_id' => $this->filial->id,
        ]);

        $this->actingAs($this->vendedor);
    }

    public function test_can_open_warranty_os_linked_to_vale()
    {
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'faturado',
        ]);

        $os = OrdemServicoGarantia::create([
            'cliente_id' => $this->cliente->id,
            'bateria_id' => $this->bateria->id,
            'vale_original_id' => $vale->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberta',
        ]);

        $this->assertDatabaseHas('ordem_servico_garantias', [
            'id' => $os->id,
            'vale_original_id' => $vale->id,
        ]);
    }

    public function test_status_change_triggers_whatsapp_job()
    {
        Queue::fake();

        $os = OrdemServicoGarantia::create([
            'cliente_id' => $this->cliente->id,
            'bateria_id' => $this->bateria->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberta',
        ]);

        $os->update(['status' => 'em_avaliacao']);

        Queue::assertPushed(SendWhatsAppNotificationJob::class, function ($job) use ($os) {
            return $job->osId === $os->id;
        });
    }

    public function test_improcedente_result_triggers_new_billing_vale()
    {
        $os = OrdemServicoGarantia::create([
            'cliente_id' => $this->cliente->id,
            'bateria_id' => $this->bateria->id,
            'filial_id' => $this->filial->id,
            'status' => 'em_avaliacao',
        ]);

        $os->update(['resultado' => 'improcedente']);

        $this->assertDatabaseHas('vales', [
            'cliente_id' => $this->cliente->id,
            'observacoes' => "Cobrança gerada por Garantia Improcedente (OS #{$os->id})",
        ]);
    }

    public function test_concluding_os_triggers_index_recalculation()
    {
        Queue::fake();

        $os = OrdemServicoGarantia::create([
            'cliente_id' => $this->cliente->id,
            'bateria_id' => $this->bateria->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberta',
        ]);

        $os->update(['status' => 'concluida', 'resultado' => 'procedente']);

        Queue::assertPushed(RecalculateProductReturnIndexJob::class, function ($job) {
            return $job->bateriaId === $this->bateria->id;
        });
    }

    public function test_return_index_calculation_logic()
    {
        // 1. Cria 10 vendas para esta bateria
        $vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'faturado',
        ]);

        ItemVale::create([
            'vale_id' => $vale->id,
            'bateria_id' => $this->bateria->id,
            'quantidade' => 10,
            'preco_unitario_original' => 450.00,
            'preco_unitario_final' => 450.00,
        ]);

        // 2. Cria 1 garantia procedente
        OrdemServicoGarantia::create([
            'cliente_id' => $this->cliente->id,
            'bateria_id' => $this->bateria->id,
            'filial_id' => $this->filial->id,
            'status' => 'concluida',
            'resultado' => 'procedente',
        ]);

        // 3. Executa o Job manualmente
        (new RecalculateProductReturnIndexJob($this->bateria->id))->handle();

        // 4. Verifica o índice (1 / 10 * 100 = 10%)
        $this->bateria->refresh();
        $this->assertEquals(10.00, (float) $this->bateria->indice_retorno);

        $this->assertDatabaseHas('indice_retornos', [
            'bateria_id' => $this->bateria->id,
            'indice_calculado' => 10.00,
        ]);
    }
}
