<?php

namespace Tests\Feature;

use App\Models\BancoPerfil;
use App\Models\Cobranca;
use App\Models\WebhookRecebido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BankingMsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste de idempotência na emissão de boleto.
     */
    public function test_boleto_emission_is_idempotent(): void
    {
        $banco = BancoPerfil::query()->create([
            'nome' => 'Sicoob',
            'codigo_banco' => '756',
            'agencia' => '0001',
            'conta' => '12345',
        ]);

        $idempotencyKey = (string) Str::uuid();
        $payload = [
            'idempotency_key' => $idempotencyKey,
            'erp_fatura_id' => 1001,
            'banco_id' => $banco->id,
            'valor' => 150.50,
            'vencimento' => now()->addDays(5)->format('Y-m-d'),
            'sacado' => ['nome' => 'Teste Sacado', 'documento' => '000.000.000-00'],
        ];

        $response1 = $this->postJson('/api/v1/boleto', $payload);
        $response1->assertStatus(200);
        $id1 = $response1->json('id');

        $response2 = $this->postJson('/api/v1/boleto', $payload);
        $response2->assertStatus(200);
        $id2 = $response2->json('id');

        $this->assertEquals($id1, $id2);
        $this->assertEquals(1, Cobranca::count());
    }

    /**
     * Teste de recebimento de webhook.
     */
    public function test_webhook_updates_cobranca_status(): void
    {
        $banco = BancoPerfil::query()->create([
            'nome' => 'PixBank',
            'codigo_banco' => '999',
            'agencia' => '1',
            'conta' => '1',
        ]);

        $cobranca = Cobranca::query()->create([
            'idempotency_key' => (string) Str::uuid(),
            'erp_fatura_id' => 2002,
            'banco_id' => $banco->id,
            'tipo' => 'pix',
            'valor' => 100.00,
            'vencimento' => now()->toDateString(),
            'txid' => 'MY-TXID-123',
            'status' => 'pendente',
        ]);

        $response = $this->postJson('/api/v1/webhook/pixbank', [
            'txid' => 'MY-TXID-123',
            'valor_pago' => 100.00,
            'status' => 'pago',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('pago', $cobranca->fresh()->status);
        $this->assertSame(1, WebhookRecebido::query()->count());
    }

    public function test_pix_endpoint_returns_qr_code_payload(): void
    {
        $banco = BancoPerfil::query()->create([
            'nome' => 'Itau',
            'codigo_banco' => '341',
            'agencia' => '0002',
            'conta' => '98765',
        ]);

        $response = $this->postJson('/api/v1/pix', [
            'idempotency_key' => (string) Str::uuid(),
            'erp_fatura_id' => 3003,
            'banco_id' => $banco->id,
            'valor' => 99.90,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'pendente')
            ->assertJsonStructure(['id', 'qrcode_pix', 'link_pagamento']);
    }

    public function test_health_endpoint_returns_service_status(): void
    {
        config(['banking.driver' => 'mock']);

        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('service', 'ms-002-bancario')
            ->assertJsonPath('status', 'ok');
    }
}
