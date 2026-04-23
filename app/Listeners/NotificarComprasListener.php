<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EstoqueBaixoEvent;
use App\Jobs\EnviarNotificacaoJob;

/**
 * Notifica o grupo de compras quando o estoque cai abaixo do nivel desejado.
 */
class NotificarComprasListener
{
    public function handle(EstoqueBaixoEvent $event): void
    {
        $target = (string) config('services.suporte.compras_email', config('mail.from.address', 'compras@example.com'));

        EnviarNotificacaoJob::dispatch(
            to: $target,
            tipo: 'compras',
            dados: [
                'bateria_id' => $event->bateria->id,
                'sku' => $event->bateria->sku,
                'saldo_atual' => $event->saldo_atual,
            ],
        );
    }
}
