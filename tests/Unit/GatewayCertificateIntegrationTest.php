<?php

namespace Tests\Unit;

use App\Models\CertificadoDigital;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Vale;
use App\Services\BankGatewayClient;
use App\Services\FiscalGatewayClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GatewayCertificateIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central/2026_05_06_114540_create_certificados_digitais_table.php',
            '--force' => true,
        ]);
    }

    public function test_fiscal_gateway_uses_subscriber_fiscal_certificate(): void
    {
        [$cliente, $vale] = $this->criarVale();

        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'nome_referencia' => 'CERT-FISCAL-A1',
            'modelo' => 'a1',
            'formato' => 'pfx',
            'prioridade' => 10,
            'status' => 'active',
        ]);

        Http::fake([
            '*/api/v1/nfe/emitir' => Http::response([
                'status' => 'emitida',
                'chave_acesso' => 'NFE123',
                'xml_autorizado' => 'generated://nfe/123.xml',
                'correlation_id' => 'idempotency-fiscal-1',
            ], 200),
        ]);

        $resposta = app(FiscalGatewayClient::class)->emitirNota($vale, 'idempotency-fiscal-1');

        $this->assertSame('emitida', $resposta['status']);
        $this->assertSame('CERT-FISCAL-A1', $resposta['certificado']['referencia']);
        Http::assertSentCount(1);
        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return str_contains($request->url(), '/api/v1/nfe/emitir')
                && isset($data['correlation_id'])
                && isset($data['tipo'])
                && isset($data['certificado']['conteudo'])
                && isset($data['certificado']['senha']);
        });
    }

    public function test_bank_gateway_uses_subscriber_bank_certificate(): void
    {
        [$cliente, $vale] = $this->criarVale();

        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'bancario',
            'nome_referencia' => 'CERT-BANK-A3',
            'modelo' => 'a3',
            'formato' => 'remote',
            'prioridade' => 10,
            'status' => 'active',
        ]);

        Http::fake([
            '*/api/v1/boleto' => Http::response([
                'status' => 'emitido',
                'nosso_numero' => 'NN123',
                'linha_digitavel' => 'linha',
                'pdf_url' => 'https://bank.local/boleto/123.pdf',
                'identificador_externo' => 'boleto-idempotency-bank-1',
            ], 200),
        ]);

        $resposta = app(BankGatewayClient::class)->emitirBoleto($vale, 'idempotency-bank-1');

        $this->assertSame('emitido', $resposta['status']);
        $this->assertSame('CERT-BANK-A3', $resposta['certificado']['referencia']);
        Http::assertSentCount(1);
        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return str_contains($request->url(), '/api/v1/boleto')
                && isset($data['idempotency_key'])
                && isset($data['valor'])
                && isset($data['sacado']['documento'])
                && isset($data['certificado']['conteudo'])
                && isset($data['certificado']['senha']);
        });
    }

    public function test_fiscal_gateway_throws_when_certificate_is_missing(): void
    {
        [, $vale] = $this->criarVale();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Nenhum certificado digital ativo encontrado para finalidade fiscal');

        app(FiscalGatewayClient::class)->emitirNota($vale, 'idempotency-fiscal-missing');
    }

    public function test_bank_gateway_throws_when_certificate_is_missing(): void
    {
        [, $vale] = $this->criarVale();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Nenhum certificado digital ativo encontrado para finalidade bancária');

        app(BankGatewayClient::class)->emitirBoleto($vale, 'idempotency-bank-missing');
    }

    private function criarVale(): array
    {
        $cliente = Cliente::factory()->create();
        $usuario = User::factory()->create();

        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $usuario->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'data_faturamento' => now(),
            'created_by' => $usuario->id,
        ]);

        return [$cliente, $vale];
    }
}
