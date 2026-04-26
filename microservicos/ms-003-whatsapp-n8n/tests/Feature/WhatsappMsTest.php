<?php

namespace Tests\Feature;

use App\Models\ContatoBlacklist;
use App\Models\WorkflowExecucao;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappMsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste de bloqueio por Blacklist.
     */
    public function test_cannot_send_to_blacklisted_number(): void
    {
        ContatoBlacklist::query()->create(['numero_tel' => '5511999999999', 'motivo' => 'Opt-out']);

        $response = $this->postJson('/api/v1/notificacao/enviar', [
            'to' => '5511999999999',
            'message' => 'Olá!',
            'evento' => 'TEST_EVENT',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'blocked');
    }

    /**
     * Teste de agendamento por Horário Comercial (Simulado fora do horário).
     */
    public function test_notification_is_scheduled_outside_commercial_hours(): void
    {
        Carbon::setTestNow(Carbon::createFromTime(22, 0));

        $response = $this->postJson('/api/v1/notificacao/enviar', [
            'to' => '5511888888888',
            'message' => 'Seu pedido saiu!',
            'evento' => 'ENTREGA_SAIU',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'scheduled');

        $this->assertDatabaseHas('fila_notificacaos', [
            'destinatario' => '5511888888888',
            'status' => 'pendente',
        ]);

        Carbon::setTestNow();
    }

    /**
     * Teste de auto-blacklist via "PARAR".
     */
    public function test_auto_blacklist_on_stop_word(): void
    {
        $response = $this->postJson('/api/v1/webhook/evolution', [
            'data' => [
                'key' => ['remoteJid' => '5511777777777@s.whatsapp.net'],
                'message' => ['conversation' => 'PARAR'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contato_blacklist', [
            'numero_tel' => '5511777777777',
        ]);
    }

    public function test_health_endpoint_returns_service_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('service', 'ms-003-whatsapp-n8n');
    }

    public function test_history_endpoint_returns_workflow_executions(): void
    {
        WorkflowExecucao::query()->create([
            'workflow_name' => 'wf-confirmacao-compra',
            'evento_trigger' => 'VALE_FATURADO',
            'status' => 'success',
            'payload_entrada' => ['pedido' => 10],
            'mensagem_enviada' => 'Mensagem teste',
            'canal' => 'whatsapp',
            'destinatario' => '5511666666666',
        ]);

        $this->getJson('/api/v1/notificacao/historico/5511666666666')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
