<?php

namespace App\Jobs;

use App\Models\NotificacaoWhatsApp;
use App\Models\OrdemServicoGarantia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $osId,
        public string $mensagem
    ) {}

    public function handle(): void
    {
        $os = OrdemServicoGarantia::with('cliente')->findOrFail($this->osId);
        $telefone = $os->cliente->telefone;

        if (!$telefone) {
            return;
        }

        // Simulação de envio via Gateway
        Log::channel('single')->info("WhatsApp Enviado para {$telefone}: {$this->mensagem}");

        // Registro interno
        NotificacaoWhatsApp::create([
            'os_garantia_id' => $os->id,
            'cliente_telefone' => $telefone,
            'status' => 'enviado',
            'mensagem' => $this->mensagem,
            'data_envio' => now(),
        ]);
    }
}
