<?php

namespace Tests\Unit;

use App\Models\CertificadoDigital;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Vale;
use App\Services\Contracts\Microservices\V1\BankSlipPayloadV1;
use App\Services\Contracts\Microservices\V1\FiscalEmissionPayloadV1;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PayloadContractsV1Test extends TestCase
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

    public function test_fiscal_payload_v1_contains_required_keys(): void
    {
        [$vale, $certificado] = $this->criarCenarioBase();
        $payload = FiscalEmissionPayloadV1::fromVale($vale, $certificado, 'corr-123');

        $this->assertSame('corr-123', $payload['correlation_id']);
        $this->assertSame('nfe', $payload['tipo']);
        $this->assertArrayHasKey('emitente', $payload);
        $this->assertArrayHasKey('itens', $payload);
        $this->assertArrayHasKey('certificado', $payload);
    }

    public function test_bank_payload_v1_contains_required_keys(): void
    {
        [$vale, $certificado] = $this->criarCenarioBase();
        $payload = BankSlipPayloadV1::fromVale($vale, $certificado, 'idem-123');

        $this->assertSame('idem-123', $payload['idempotency_key']);
        $this->assertArrayHasKey('valor', $payload);
        $this->assertArrayHasKey('vencimento', $payload);
        $this->assertArrayHasKey('sacado', $payload);
        $this->assertArrayHasKey('certificado', $payload);
    }

    private function criarCenarioBase(): array
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

        $certificado = CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'status' => 'active',
        ]);

        return [$vale, $certificado];
    }
}
