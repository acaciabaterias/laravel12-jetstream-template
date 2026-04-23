<?php

namespace App\Services;

use App\Models\EstoqueSaldo;
use App\Models\ItemVale;
use App\Models\ReservaEstoque;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaEstoqueService
{
    public function reservar(Vale $vale, ItemVale $itemVale, ?User $user = null): ReservaEstoque
    {
        return DB::transaction(function () use ($vale, $itemVale) {
            $saldo = EstoqueSaldo::query()
                ->with(['deposito', 'bateria'])
                ->where('bateria_id', $itemVale->bateria_id)
                ->get()
                ->first(function (EstoqueSaldo $saldo) use ($itemVale): bool {
                    return $this->saldoDisponivel($saldo->bateria_id, $saldo->deposito_id) >= $itemVale->quantidade;
                });

            if (! $saldo) {
                throw ValidationException::withMessages([
                    'quantidade' => 'Saldo insuficiente para reservar a bateria selecionada.',
                ]);
            }

            return ReservaEstoque::query()->create([
                'vale_id' => $vale->id,
                'item_vale_id' => $itemVale->id,
                'bateria_id' => $itemVale->bateria_id,
                'deposito_id' => $saldo->deposito_id,
                'quantidade' => $itemVale->quantidade,
                'status' => 'reservada',
            ]);
        });
    }

    public function estornarPorVale(Vale $vale): void
    {
        ReservaEstoque::query()
            ->where('vale_id', $vale->id)
            ->where('status', 'reservada')
            ->each(function (ReservaEstoque $reserva): void {
                $reserva->update(['status' => 'estornada']);
            });
    }

    public function confirmarPorVale(Vale $vale, EstoqueSaldoService $estoqueSaldoService, ?User $user = null): void
    {
        DB::transaction(function () use ($vale, $estoqueSaldoService, $user): void {
            $reservas = ReservaEstoque::query()
                ->where('vale_id', $vale->id)
                ->where('status', 'reservada')
                ->with(['bateria', 'deposito'])
                ->lockForUpdate()
                ->get();

            foreach ($reservas as $reserva) {
                $estoqueSaldoService->registrarMovimentacao(
                    bateria: $reserva->bateria,
                    deposito: $reserva->deposito,
                    quantidade: (int) $reserva->quantidade,
                    tipoOperacao: 'saida',
                    user: $user,
                    origem: 'pedido_venda',
                    justificativa: 'Conversao de vale em pedido de venda',
                );

                $reserva->update(['status' => 'confirmada']);
            }
        });
    }

    public function saldoDisponivel(int $bateriaId, int $depositoId): int
    {
        $saldoAtual = (int) (EstoqueSaldo::query()
            ->where('bateria_id', $bateriaId)
            ->where('deposito_id', $depositoId)
            ->value('quantidade_atual') ?? 0);

        $reservado = (int) ReservaEstoque::query()
            ->where('bateria_id', $bateriaId)
            ->where('deposito_id', $depositoId)
            ->where('status', 'reservada')
            ->sum('quantidade');

        return $saldoAtual - $reservado;
    }
}
