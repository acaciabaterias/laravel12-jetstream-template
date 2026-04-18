<?php

namespace App\Services;

use App\Models\ItemVale;
use App\Models\OrdemServicoGarantia;
use Illuminate\Support\Collection;

class TraceabilityService
{
    /**
     * Localiza o histórico completo de um produto pelo seu número de série.
     */
    public function findBySerialNumber(string $sn): array
    {
        $venda = ItemVale::where('numero_serie', $sn)
            ->with(['vale.cliente', 'vale.vendedor', 'bateria'])
            ->first();

        $garantias = OrdemServicoGarantia::where('numero_serie', $sn)
            ->with(['filial', 'cliente'])
            ->latest()
            ->get();

        return [
            'venda' => $venda,
            'cliente' => $venda?->vale?->cliente,
            'garantias' => $garantias,
            'produto' => $venda?->bateria,
        ];
    }
}
