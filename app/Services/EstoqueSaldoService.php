<?php

namespace App\Services;

use App\Models\Bateria;
use App\Models\Deposito;
use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueSaldo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstoqueSaldoService
{
    public function registrarMovimentacao(
        Bateria $bateria,
        Deposito $deposito,
        int $quantidade,
        string $tipoOperacao,
        ?User $user = null,
        ?string $origem = null,
        ?string $justificativa = null,
    ): EstoqueMovimentacao {
        return DB::transaction(function () use ($bateria, $deposito, $quantidade, $tipoOperacao, $user, $origem, $justificativa) {
            $saldo = EstoqueSaldo::query()->firstOrCreate(
                [
                    'bateria_id' => $bateria->id,
                    'deposito_id' => $deposito->id,
                ],
                [
                    'quantidade_atual' => 0,
                ],
            );

            $delta = $this->resolverDelta($tipoOperacao, $quantidade);
            $novoSaldo = $saldo->quantidade_atual + $delta;

            if ($novoSaldo < 0) {
                throw ValidationException::withMessages([
                    'quantidade' => 'A movimentacao deixaria o estoque negativo para a bateria selecionada.',
                ]);
            }

            $saldo->update([
                'quantidade_atual' => $novoSaldo,
            ]);

            return EstoqueMovimentacao::query()->create([
                'bateria_id' => $bateria->id,
                'deposito_id' => $deposito->id,
                'user_id' => $user?->id,
                'tipo_operacao' => $tipoOperacao,
                'origem' => $origem,
                'quantidade' => $quantidade,
                'justificativa' => $justificativa,
                'data_movimentacao' => now(),
            ]);
        });
    }

    protected function resolverDelta(string $tipoOperacao, int $quantidade): int
    {
        return match ($tipoOperacao) {
            'entrada', 'ajuste_positivo' => $quantidade,
            'saida', 'ajuste_negativo' => $quantidade * -1,
            default => throw ValidationException::withMessages([
                'tipo_operacao' => 'Tipo de operacao de estoque invalido.',
            ]),
        };
    }
}
