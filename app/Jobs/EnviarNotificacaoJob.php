<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Encapsula envio de notificacoes para WhatsApp ou e-mail.
 */
class EnviarNotificacaoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function __construct(
        public string $to,
        public string $tipo,
        public array $dados,
    ) {}

    public function handle(): void
    {
        $mensagem = (string) ($this->dados['mensagem'] ?? json_encode($this->dados));

        if (in_array($this->tipo, ['whatsapp', 'compras'], true)) {
            Http::post(rtrim((string) config('services.ms_whatsapp.url', 'http://localhost:8003'), '/').'/v1/notificacao/enviar', [
                'numero' => $this->to,
                'cliente_id' => $this->dados['cliente_id'] ?? null,
                'mensagem' => $mensagem,
                'template' => $this->dados['template'] ?? $this->tipo,
            ]);

            return;
        }

        Mail::raw($mensagem, function ($message): void {
            $message->to($this->to)->subject((string) ($this->dados['assunto'] ?? 'Notificacao BateriaExpert'));
        });
    }
}
