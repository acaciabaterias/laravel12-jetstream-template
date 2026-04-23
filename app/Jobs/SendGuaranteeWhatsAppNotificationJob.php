<?php

namespace App\Jobs;

use App\Models\NotificacaoWhatsApp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGuaranteeWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $notificacaoId, public bool $shouldFail = false) {}

    public function handle(): void
    {
        $notificacao = NotificacaoWhatsApp::query()->findOrFail($this->notificacaoId);

        if ($this->shouldFail) {
            $notificacao->update([
                'status' => 'falha',
                'tracking_error' => 'Gateway indisponivel',
            ]);

            return;
        }

        $notificacao->update([
            'status' => 'enviado',
            'data_envio' => now(),
            'identificador_externo' => 'wa-'.$notificacao->id,
            'tracking_error' => null,
        ]);
    }
}
