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
            $this->validarJustificativaParaAjusteManual($origem, $justificativa);

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

    public function transferirEntreDepositos(
        Bateria $bateria,
        Deposito $depositoOrigem,
        Deposito $depositoDestino,
        int $quantidade,
        ?User $user = null,
        ?string $justificativa = null,
    ): array {
        if ($depositoOrigem->id === $depositoDestino->id) {
            throw ValidationException::withMessages([
                'deposito_destino_id' => 'O depósito de destino deve ser diferente do depósito de origem.',
            ]);
        }

        return DB::transaction(function () use ($bateria, $depositoOrigem, $depositoDestino, $quantidade, $user, $justificativa) {
            $saida = $this->registrarMovimentacao(
                bateria: $bateria,
                deposito: $depositoOrigem,
                quantidade: $quantidade,
                tipoOperacao: 'saida',
                user: $user,
                origem: 'transferencia_estoque',
                justificativa: $justificativa ?: 'Transferência entre depósitos',
            );

            $entrada = $this->registrarMovimentacao(
                bateria: $bateria,
                deposito: $depositoDestino,
                quantidade: $quantidade,
                tipoOperacao: 'entrada',
                user: $user,
                origem: 'transferencia_estoque',
                justificativa: $justificativa ?: 'Transferência entre depósitos',
            );

            return [
                'saida' => $saida,
                'entrada' => $entrada,
            ];
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

    protected function validarJustificativaParaAjusteManual(?string $origem, ?string $justificativa): void
    {
        if ($origem !== 'ajuste_manual') {
            return;
        }

        if (trim((string) $justificativa) === '') {
            throw ValidationException::withMessages([
                'justificativa' => 'A justificativa é obrigatória para ajustes manuais de estoque.',
            ]);
        }
    }
}
