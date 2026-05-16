<?php

namespace Tests\Feature;

use App\Models\BancoProvider;
use App\Models\Consentimento;
use App\Models\TransacaoBancaria;
use App\Services\EncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenFinanceMsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste de criptografia dos tokens.
     */
    public function test_token_encryption_is_secure(): void
    {
        config(['services.openfinance.token_key' => 'qwertyuiopasdfghjklzxcvbnm123456']);
        $service = new EncryptionService;

        $original = 'my-secret-bank-token-123';
        $encrypted = $service->encrypt($original);

        $this->assertNotEquals($original, $encrypted);
        $this->assertEquals($original, $service->decrypt($encrypted));
    }

    /**
     * Teste de deduplicação de transações.
     */
    public function test_transaction_deduplication_prevents_repeats(): void
    {
        $provider = BancoProvider::query()->create([
            'nome' => 'Banco X',
            'codigo_banco' => '001',
            'provider' => 'mock',
        ]);

        $consentimento = Consentimento::query()->create([
            'empresa_id' => 1,
            'provider_id' => $provider->id,
            'status' => 'ativo',
            'access_token_encrypted' => 'any',
        ]);

        $txData = [
            'consentimento_id' => $consentimento->id,
            'tx_id_original' => 'TX123',
            'data_lancamento' => '2026-04-18',
            'descricao' => 'PAGTO LUZ',
            'valor' => -150.00,
            'tipo' => 'debito',
        ];

        $hash = TransacaoBancaria::generateHash(array_merge($txData, ['data' => $txData['data_lancamento']]));

        TransacaoBancaria::query()->create(array_merge($txData, ['deduplicacao_hash' => $hash]));
        $countBefore = TransacaoBancaria::count();

        if (! TransacaoBancaria::query()->where('deduplicacao_hash', $hash)->exists()) {
            TransacaoBancaria::query()->create(array_merge($txData, ['deduplicacao_hash' => $hash]));
        }

        $this->assertEquals(1, TransacaoBancaria::count());
        $this->assertEquals($countBefore, TransacaoBancaria::count());
    }

    public function test_health_endpoint_returns_service_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('service', 'ms-004-openfinance');
    }
}
