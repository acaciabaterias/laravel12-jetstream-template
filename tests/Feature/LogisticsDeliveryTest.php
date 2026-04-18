<?php

namespace Tests\Feature;

use App\Jobs\ConvertValeToPedidoJob;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\PontoEntrega;
use App\Models\RotaEntrega;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LogisticsDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private Filial $filial;
    private User $vendedor;
    private User $entregador;
    private Cliente $cliente;
    private Vale $vale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filial = Filial::factory()->create();
        $this->vendedor = User::factory()->create(['filial_id' => $this->filial->id]);
        $this->entregador = User::factory()->create(['filial_id' => $this->filial->id]);
        
        // Carga de infraestrutura necessária para o Job de faturamento
        \App\Models\Deposito::create([
            'nome' => 'Depósito Central Teste',
            'filial_id' => $this->filial->id,
            'is_principal' => true
        ]);
        
        $this->cliente = Cliente::create([
            'cnpj' => '12.345.678/0001-99',
            'razao_social' => 'Logistics Test Client',
            'nome_fantasia' => 'Test Logist',
            'email_contato' => 'log@test.com',
            'telefone' => '1199999999',
            'subdominio' => 'log-test-' . uniqid(),
            'supabase_project_ref' => 'ref-log-' . uniqid(),
            'supabase_url' => 'http://test.co',
            'supabase_db_host' => 'host',
            'supabase_db_password' => 'pass',
            'supabase_anon_key' => 'key',
            'supabase_service_role_key' => 'key',
            'saldo_sucata_kg' => 100.0, // Começa com débito de 100kg
        ]);

        $this->vale = Vale::create([
            'cliente_id' => $this->cliente->id,
            'vendedor_id' => $this->vendedor->id,
            'filial_id' => $this->filial->id,
            'status' => 'aberto',
        ]);
        
        $this->actingAs($this->entregador);
    }

    public function test_sync_api_updates_point_and_registers_payments()
    {
        $rota = RotaEntrega::create([
            'entregador_id' => $this->entregador->id,
            'filial_id' => $this->filial->id,
            'data_rota' => now()->toDateString(),
            'status' => 'ativa',
        ]);

        $ponto = PontoEntrega::create([
            'rota_entrega_id' => $rota->id,
            'vale_id' => $this->vale->id,
            'filial_id' => $this->filial->id,
            'ordem_parada' => 1,
            'status' => 'pendente',
        ]);

        $payload = [
            'updates' => [
                [
                    'ponto_entrega_id' => $ponto->id,
                    'status' => 'concluido',
                    'peso_sucata_coletado' => 15.5,
                    'recebimentos' => [
                        ['valor' => 100.0, 'metodo' => 'pix'],
                        ['valor' => 50.0, 'metodo' => 'dinheiro'],
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/logistics/sync', $payload);

        $response->assertStatus(200);
        
        $ponto->refresh();
        $this->assertEquals('concluido', $ponto->status);
        $this->assertEquals(15.5, (float) $ponto->peso_sucata_coletado);
        
        $this->assertDatabaseHas('recebimento_movels', [
            'ponto_entrega_id' => $ponto->id,
            'valor' => 100.0,
            'metodo' => 'pix'
        ]);
    }

    public function test_ponto_concluido_triggers_billing_and_sucata_reconciliation()
    {
        Queue::fake();

        $rota = RotaEntrega::create([
            'entregador_id' => $this->entregador->id,
            'filial_id' => $this->filial->id,
            'data_rota' => now()->toDateString(),
            'status' => 'ativa',
        ]);

        $ponto = PontoEntrega::create([
            'rota_entrega_id' => $rota->id,
            'vale_id' => $this->vale->id,
            'filial_id' => $this->filial->id,
            'status' => 'pendente',
        ]);

        // Ativa o Observer ao mudar status para concluido e informar peso
        $ponto->update([
            'status' => 'concluido',
            'peso_sucata_coletado' => 10.0
        ]);

        // 1. Verifica se o Job de Faturamento foi disparado
        Queue::assertPushed(ConvertValeToPedidoJob::class, function ($job) {
            return $job->valeId === $this->vale->id;
        });

        // 2. Verifica se o saldo de sucata do cliente foi abatido (100 - 10 = 90)
        $this->cliente->refresh();
        $this->assertEquals(90.0, (float) $this->cliente->saldo_sucata_kg);
    }

    public function test_sync_fails_if_ponto_does_not_exist()
    {
        $response = $this->postJson('/api/logistics/sync', [
            'updates' => [
                ['ponto_entrega_id' => 9999, 'status' => 'concluido']
            ]
        ]);

        $response->assertStatus(422);
    }
}
