<?php

namespace App\Services;

use App\Models\Bateria;

class NetPriceCalculator
{
    public function calculate(Bateria $bateria, bool $devolveuSucata): array
    {
        $precoOriginal = (float) ($bateria->preco_venda ?? 0);
        $surcharge = $devolveuSucata
            ? 0
            : (float) ($bateria->peso_sucata_kg ?? 0) * (float) ($bateria->valor_base_sucata_kg ?? 0);

        return [
            'preco_unitario_original' => round($precoOriginal, 2),
            'preco_unitario_final' => round($precoOriginal + $surcharge, 2),
            'acrescimo_sucata' => round($surcharge, 2),
        ];
    }
}
