<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Bateria;
use App\Models\IndiceRetornoProduto;
use App\Models\ItemVale;
use App\Models\OrdemServicoGarantia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Recalcula os indices de retorno de todas as baterias ativas.
 */
class AtualizarIndiceRetornoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        Bateria::query()->whereNull('deleted_at')->each(function (Bateria $bateria): void {
            $vendasTotal = (int) ItemVale::query()
                ->where('bateria_id', $bateria->id)
                ->whereHas('vale', fn ($query) => $query->where('status', 'faturado'))
                ->sum('quantidade');

            $garantiasProcedentes = OrdemServicoGarantia::query()
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
        });
    }
}
