<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OSConcluidaEvent;
use App\Models\IndiceRetornoProduto;
use App\Models\ItemVale;

/**
 * Recalcula o indice de retorno do produto ao concluir a OS.
 */
class AtualizarIndiceRetornoListener
{
    public function handle(OSConcluidaEvent $event): void
    {
        $bateria = $event->os->bateria;

        $vendasTotal = (int) ItemVale::query()
            ->where('bateria_id', $bateria->id)
            ->whereHas('vale', fn ($query) => $query->where('status', 'faturado'))
            ->sum('quantidade');

        $garantiasProcedentes = (int) $event->os->newQuery()
            ->where('bateria_id', $bateria->id)
            ->where('resultado', 'procedente')
            ->count();

        $indice = $vendasTotal > 0
            ? round(($garantiasProcedentes / $vendasTotal) * 100, 4)
            : 0.0;

        IndiceRetornoProduto::query()->updateOrCreate(
            [
                'bateria_id' => $bateria->id,
                'periodo_inicio' => now()->startOfMonth()->toDateString(),
                'periodo_fim' => now()->endOfMonth()->toDateString(),
            ],
            [
                'total_vendidas' => $vendasTotal,
                'total_garantias' => $garantiasProcedentes,
                'indice_calculado' => $indice,
            ],
        );

        $atributos = (array) ($bateria->atributos_dinamicos ?? []);
        $atributos['indice_retorno'] = $indice;

        $bateria->forceFill([
            'atributos_dinamicos' => $atributos,
        ])->save();
    }
}
