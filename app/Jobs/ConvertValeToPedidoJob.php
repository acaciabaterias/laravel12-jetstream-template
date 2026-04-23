<?php

namespace App\Jobs;

use App\Models\Cliente;
use App\Models\ContaSucataMovimentacao;
use App\Models\PedidoVenda;
use App\Models\Vale;
use App\Services\EstoqueSaldoService;
use App\Services\ReservaEstoqueService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConvertValeToPedidoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $valeId, public ?int $actorId = null) {}

    public function handle(ReservaEstoqueService $reservaEstoqueService, EstoqueSaldoService $estoqueSaldoService): void
    {
        DB::transaction(function () use ($reservaEstoqueService, $estoqueSaldoService): void {
            $vale = Vale::query()
                ->with(['itens.bateria', 'reservas.deposito'])
                ->findOrFail($this->valeId);

            if ($vale->status !== 'aberto') {
                throw ValidationException::withMessages([
                    'vale' => 'Apenas vales abertos podem ser convertidos em pedido.',
                ]);
            }

            $actor = $this->actorId ? \App\Models\User::query()->find($this->actorId) : null;

            $reservaEstoqueService->confirmarPorVale($vale, $estoqueSaldoService, $actor);

            PedidoVenda::query()->create([
                'vale_id' => $vale->id,
                'cliente_id' => $vale->cliente_id,
                'data_emissao' => now(),
                'valor_total' => $vale->valor_total,
                'status' => 'faturado',
                'nf_referencia' => null,
            ]);

            $debitoSucata = $vale->itens
                ->filter(fn ($item) => ! $item->flag_devolveu_sucata)
                ->sum(fn ($item) => ((float) $item->preco_unitario_final - (float) $item->preco_unitario_original) * (int) $item->quantidade);

            if ($debitoSucata > 0) {
                ContaSucataMovimentacao::query()->create([
                    'entidade_tipo' => Cliente::class,
                    'entidade_id' => $vale->cliente_id,
                    'tipo_movimento' => 'debito',
                    'quantidade_kg' => 1,
                    'valor_unitario' => $debitoSucata,
                    'saldo_resultante' => ((float) ContaSucataMovimentacao::query()->latest('id')->value('saldo_resultante')) - $debitoSucata,
                    'origem' => 'vale_sem_sucata',
                ]);
            }

            $vale->update([
                'status' => 'faturado',
                'data_faturamento' => now(),
            ]);
        });
    }
}
