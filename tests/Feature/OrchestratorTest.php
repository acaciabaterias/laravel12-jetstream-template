<?php

namespace Tests\Feature;

use App\Models\Filial;
use App\Models\FilaContingencia;
use App\Services\Gateways\FiscalGateway;
use App\Services\Gateways\BankGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrchestratorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste: Se o MS-Fiscal falhar, entrar em contingência.
     */
    public function test_enters_fiscal_contingency_on_failure()
    {
        Http::fake([
            'http://localhost:8001/api/v1/emissao' => Http::response([], 500)
        ]);

        $filial = Filial::factory()->create([
            'ms_fiscal_api_key' => 'test-key'
        ]);

        $gateway = new FiscalGateway();
        $payload = ['vale_id' => 1, 'items' => []];

        $resultado = $gateway->emitir($filial, $payload);

        $this->assertEquals('contingencia', $resultado['status']);
        $this->assertDatabaseHas('fila_contingencia', [
            'filial_id' => $filial->id,
            'tipo' => 'fiscal',
            'status' => 'pendente'
        ]);
    }

    /**
     * Teste: Retry Engine processa item com sucesso.
     */
    public function test_retry_engine_processes_item_successfully()
    {
        Http::fake([
            'http://localhost:8001/api/v1/emissao' => Http::response(['status' => 'success'], 200)
        ]);

        $filial = Filial::factory()->create();
        $item = FilaContingencia::create([
            'filial_id' => $filial->id,
            'tipo' => 'fiscal',
            'payload' => ['vale_id' => 1],
            'status' => 'pendente',
            'proxima_tentativa' => now()->subMinute()
        ]);

        $this->artisan('orquestrador:retry');

        $this->assertDatabaseHas('fila_contingencia', [
            'id' => $item->id,
            'status' => 'concluido'
        ]);
    }

    /**
     * Teste: Alerta de Falha Crítica após 10 tentativas.
     */
    public function test_alerts_support_on_critical_failure()
    {
        Http::fake([
            'http://localhost:8001/api/v1/emissao' => Http::response([], 500),
            'http://localhost:8003/api/v1/notificacao/enviar' => Http::response(['status' => 'success'], 200)
        ]);

        $filial = Filial::factory()->create();
        $item = FilaContingencia::create([
            'filial_id' => $filial->id,
            'tipo' => 'fiscal',
            'payload' => ['vale_id' => 1],
            'status' => 'pendente',
            'tentativas' => 9,
            'proxima_tentativa' => now()->subMinute()
        ]);

        $this->artisan('orquestrador:retry');

        $this->assertDatabaseHas('fila_contingencia', [
            'id' => $item->id,
            'status' => 'falha_critica'
        ]);

        // Verificar se chamou o MS-Whatsapp
        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8003/api/v1/notificacao/enviar');
    }
}
