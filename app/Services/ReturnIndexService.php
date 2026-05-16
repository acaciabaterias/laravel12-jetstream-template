<?php

namespace App\Services;

use App\Models\IndiceRetornoProduto;
use App\Models\ItemVale;
use App\Models\OrdemServicoGarantia;

class ReturnIndexService
{
    public function refreshForBattery(int $bateriaId): IndiceRetornoProduto
    {
        $periodoInicio = now()->startOfMonth();
        $periodoFim = now()->endOfMonth();

        $totalVendidas = ItemVale::query()
            ->where('bateria_id', $bateriaId)
            ->whereHas('vale', fn ($query) => $query->where('status', 'faturado'))
            ->sum('quantidade');

        $totalGarantias = OrdemServicoGarantia::query()
            ->where('bateria_id', $bateriaId)
            ->whereBetween('created_at', [$periodoInicio, $periodoFim])
            ->count();

        $indice = $totalVendidas > 0
            ? round($totalGarantias / $totalVendidas, 4)
            : 0;

        return IndiceRetornoProduto::query()->updateOrCreate(
            [
                'bateria_id' => $bateriaId,
                'periodo_inicio' => $periodoInicio->toDateString(),
                'periodo_fim' => $periodoFim->toDateString(),
            ],
            [
                'total_vendidas' => $totalVendidas,
                'total_garantias' => $totalGarantias,
                'indice_calculado' => $indice,
            ],
        );
    }
}
