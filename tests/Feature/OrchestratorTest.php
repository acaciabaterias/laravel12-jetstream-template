<?php

namespace Tests\Feature;

use App\Models\FilaContingencia;
use App\Models\Filial;
use App\Services\Gateways\FiscalGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrchestratorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste: Se o MS-Fiscal falhar, entrar em contingência.
     */
    public function test_enters_fiscal_contingency_on_failure()
    {
        $fiscalUrl = rtrim((string) config('services.ms_fiscal.url'), '/').'/api/v1/emissao';

        Http::fake([
            $fiscalUrl => Http::response([], 500),
        ]);

        $filial = Filial::factory()->create();

        $gateway = new FiscalGateway;
        $payload = ['vale_id' => 1, 'items' => []];

        $resultado = $gateway->emitir($filial, $payload);

        $this->assertEquals('contingencia', $resultado['status']);
        $this->assertDatabaseHas('filas_contingencia', [
            'tipo_integracao' => 'fiscal',
            'status' => 'pendente',
        ]);
    }

    /**
     * Teste: Retry Engine processa item com sucesso.
     */
    public function test_retry_engine_processes_item_successfully()
    {
        $fiscalUrl = rtrim((string) config('services.ms_fiscal.url'), '/').'/api/v1/emissao';

        Http::fake([
            $fiscalUrl => Http::response(['status' => 'success'], 200),
        ]);

        $item = FilaContingencia::create([
            'tipo_integracao' => 'fiscal',
            'payload' => ['vale_id' => 1],
            'status' => 'pendente',
            'proxima_tentativa' => now()->subMinute(),
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $this->artisan('orquestrador:retry');

        $this->assertDatabaseHas('filas_contingencia', [
            'id' => $item->id,
            'status' => 'concluido',
        ]);
    }

    /**
     * Teste: Alerta de Falha Crítica após 10 tentativas.
     */
    public function test_alerts_support_on_critical_failure()
    {
        $fiscalUrl = rtrim((string) config('services.ms_fiscal.url'), '/').'/api/v1/emissao';
        $whatsAppUrl = rtrim((string) config('services.ms_whatsapp.url'), '/').'/api/v1/notificacao/enviar';

        Http::fake([
            $fiscalUrl => Http::response([], 500),
            $whatsAppUrl => Http::response(['status' => 'success'], 200),
        ]);

        $item = FilaContingencia::create([
            'tipo_integracao' => 'fiscal',
            'payload' => ['vale_id' => 1],
            'status' => 'pendente',
            'tentativas' => 9,
            'proxima_tentativa' => now()->subMinute(),
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $this->artisan('orquestrador:retry');

        $this->assertDatabaseHas('filas_contingencia', [
            'id' => $item->id,
            'status' => 'falha_critica',
        ]);

        // Verificar se chamou o MS-Whatsapp
        Http::assertSent(fn ($request) => $request->url() === $whatsAppUrl);
    }
}
