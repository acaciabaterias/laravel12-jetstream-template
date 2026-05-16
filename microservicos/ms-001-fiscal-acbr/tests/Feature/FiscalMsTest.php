<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiscalMsTest extends TestCase
{
    use RefreshDatabase;

    public function test_emitir_nfe_via_mock_returns_authorized(): void
    {
        $payload = [
            'vale_id' => 123,
            'tipo' => 'NFe',
            'correlation_id' => 'vale-123',
            'customer' => ['name' => 'John Doe', 'doc' => '12345678901'],
            'items' => [
                ['sku' => 'BAT-001', 'price' => 500.00],
            ],
        ];

        config(['acbr.driver' => 'mock']);

        $response = $this->postJson('/api/v1/nfe/emitir', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'authorized');

        $this->assertDatabaseHas('nota_fiscal_jobs', [
            'vale_id' => 123,
            'status' => 'authorized',
            'correlation_id' => 'vale-123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'acao' => 'EMISSAO_SUCCESS',
        ]);
    }

    public function test_emitir_nfe_sends_to_contingency_when_driver_fails(): void
    {
        $payload = [
            'vale_id' => 456,
            'tipo' => 'NFe',
            'correlation_id' => 'vale-456',
            'customer' => ['name' => 'Jane Doe', 'doc' => '12345678901'],
            'items' => [['sku' => 'BAT-002', 'price' => 300.00]],
        ];

        config(['acbr.driver' => 'real']);

        $response = $this->postJson('/api/v1/nfe/emitir', $payload);

        $response->assertStatus(202)
            ->assertJsonPath('status', 'contingency');

        $this->assertDatabaseHas('nota_fiscal_jobs', [
            'vale_id' => 456,
            'status' => 'contingency',
        ]);

        $this->assertDatabaseHas('contingencia_queue', [
            'status' => 'pending',
        ]);
    }

    public function test_health_endpoint_returns_service_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('service', 'ms-001-fiscal-acbr');
    }

    public function test_contingency_queue_endpoint_lists_pending_items(): void
    {
        \App\Models\NotaFiscalJob::query()->create([
            'vale_id' => 789,
            'tipo' => 'NFe',
            'payload' => ['foo' => 'bar'],
            'status' => 'contingency',
            'correlation_id' => 'vale-789',
        ]);

        \App\Models\ContingenciaQueue::query()->create([
            'nota_id' => \App\Models\NotaFiscalJob::query()->first()->id,
            'status' => 'pending',
        ]);

        $this->getJson('/api/v1/contingencia/fila')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
