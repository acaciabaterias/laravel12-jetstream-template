<?php

namespace App\Services;

use App\Models\RotaEntrega;
use Illuminate\Validation\ValidationException;

class RouteCloseValidator
{
    public function assertCanClose(RotaEntrega $rotaEntrega): void
    {
        $rotaEntrega->loadMissing('pontos.recebimentos');

        $pendencias = $rotaEntrega->pontos->contains(function ($ponto): bool {
            return $ponto->status !== 'concluido'
                || $ponto->recebimentos->contains(fn ($recebimento) => ! $recebimento->status_sincronizado);
        });

        if ($pendencias) {
            throw ValidationException::withMessages([
                'rota' => 'A rota ainda possui pontos pendentes ou recebimentos nao sincronizados.',
            ]);
        }
    }
}
