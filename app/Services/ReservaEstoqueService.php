<?php

namespace App\Services;

use App\Models\EstoqueMovimentacao;
use Illuminate\Support\Facades\DB;

class ReservaEstoqueService
{
    /**
     * Tenta reservar fisicamente o estoque disparando uma transação "tipo = reserva"
     */
    public function reservar(int $bateriaId, int $depositoId, int $quantidade, int $filialId, int $userId, int $valeId): void
    {
        DB::transaction(function () use ($bateriaId, $depositoId, $quantidade, $filialId, $userId, $valeId) {
            EstoqueMovimentacao::create([
                'bateria_id' => $bateriaId,
                'filial_id' => $filialId,
                'deposito_id' => $depositoId,
                'user_id' => $userId,
                'tipo' => 'reserva',
                'quantidade' => $quantidade,
                'origem' => 'Venda PDV',
                'referencia' => 'Vale ID: '.$valeId,
            ]);
        });
    }

    /**
     * Estorna uma reserva disparando uma transação "tipo = estorno_reserva"
     */
    public function estornar(int $bateriaId, int $depositoId, int $quantidade, int $filialId, int $userId, int $valeId): void
    {
        DB::transaction(function () use ($bateriaId, $depositoId, $quantidade, $filialId, $userId, $valeId) {
            EstoqueMovimentacao::create([
                'bateria_id' => $bateriaId,
                'filial_id' => $filialId,
                'deposito_id' => $depositoId,
                'user_id' => $userId,
                'tipo' => 'estorno_reserva',
                'quantidade' => $quantidade,
                'origem' => 'Cancelamento PDV / Estorno Item',
                'referencia' => 'Vale ID: '.$valeId,
            ]);
        });
    }
}
