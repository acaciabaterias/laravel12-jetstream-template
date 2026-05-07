<?php

namespace App\Services\Integration;

use App\Models\Vale;

class OutboxEventPayloads
{
    /**
     * @return array<string, mixed>
     */
    public function forValeFaturado(Vale $vale, int $pedidoVendaId): array
    {
        $vale->loadMissing('itens');

        return [
            'vale_id' => $vale->id,
            'pedido_venda_id' => $pedidoVendaId,
            'cliente_id' => $vale->cliente_id,
            'valor_total' => $vale->valor_total,
            'faturado_em' => optional($vale->data_faturamento)->toIso8601String(),
            'itens' => $vale->itens->map(fn ($item): array => [
                'bateria_id' => $item->bateria_id,
                'quantidade' => $item->quantidade,
                'preco_unitario_final' => $item->preco_unitario_final,
            ])->values()->all(),
        ];
    }
}
