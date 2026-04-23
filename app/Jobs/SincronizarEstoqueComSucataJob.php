<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Bateria;
use App\Models\ContaSucataMovimentacao;
use App\Models\EstoqueSaldo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sincroniza o saldo de estoque com a conta sucata projetada.
 */
class SincronizarEstoqueComSucataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        Bateria::query()->where('tem_logistica_reversa', true)->each(function (Bateria $bateria): void {
            $quantidadeEstoque = (int) EstoqueSaldo::query()
                ->where('bateria_id', $bateria->id)
                ->sum('quantidade_atual');

            $quantidadeKg = round($quantidadeEstoque * (float) $bateria->peso_sucata_kg, 2);
            $saldoResultante = round($quantidadeKg * (float) $bateria->valor_base_sucata_kg, 2);

            ContaSucataMovimentacao::query()->updateOrCreate(
                [
                    'entidade_tipo' => Bateria::class,
                    'entidade_id' => $bateria->id,
                    'origem' => 'sync_estoque',
                ],
                [
                    'tipo_movimento' => 'ajuste',
                    'quantidade_kg' => $quantidadeKg,
                    'valor_unitario' => (float) $bateria->valor_base_sucata_kg,
                    'saldo_resultante' => $saldoResultante,
                ],
            );
        });
    }
}
