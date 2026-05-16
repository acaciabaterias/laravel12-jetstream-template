<?php

namespace App\Livewire;

use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueSaldo;
use App\Models\ItemVale;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class EstoqueDashboard extends Component
{
    public string $filtroBusca = '';

    public function mount(): void
    {
        Gate::authorize('acesso-estoque');
    }

    public function render(): View
    {
        $saldos = EstoqueSaldo::query()
            ->with(['bateria', 'deposito'])
            ->when($this->filtroBusca !== '', function ($query): void {
                $query->whereHas('bateria', function ($batteryQuery): void {
                    $batteryQuery->where('sku', 'like', '%'.$this->filtroBusca.'%')
                        ->orWhere('marca', 'like', '%'.$this->filtroBusca.'%');
                });
            })
            ->orderByDesc('quantidade_atual')
            ->get();

        $periodoSaidas = collect(range(0, 6))
            ->map(fn (int $offset) => now()->subDays(6 - $offset)->startOfDay());

        $movimentacoesSaida = EstoqueMovimentacao::query()
            ->where('tipo_operacao', 'saida')
            ->where('data_movimentacao', '>=', now()->subDays(6)->startOfDay())
            ->orderBy('data_movimentacao')
            ->get()
            ->groupBy(fn (EstoqueMovimentacao $movimentacao): string => $movimentacao->data_movimentacao->format('Y-m-d'));

        $maisVendidos = ItemVale::query()
            ->selectRaw('bateria_id, SUM(quantidade) as total_vendido')
            ->with('bateria')
            ->groupBy('bateria_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $limiteShelfLife = (int) config('inventory.shelf_life_days', 90);
        $ultimaEntradaPorSaldo = EstoqueMovimentacao::query()
            ->where('tipo_operacao', 'entrada')
            ->get()
            ->groupBy(fn (EstoqueMovimentacao $movimentacao): string => $movimentacao->bateria_id.'-'.$movimentacao->deposito_id)
            ->map(fn ($movimentacoes) => $movimentacoes->max('data_movimentacao'));

        $alertasShelfLife = $saldos
            ->map(function (EstoqueSaldo $saldo) use ($ultimaEntradaPorSaldo, $limiteShelfLife) {
                $chave = $saldo->bateria_id.'-'.$saldo->deposito_id;
                $ultimaEntrada = $ultimaEntradaPorSaldo->get($chave);

                if (! $ultimaEntrada) {
                    return null;
                }

                $diasEmEstoque = now()->diffInDays($ultimaEntrada);
                if ($diasEmEstoque <= $limiteShelfLife) {
                    return null;
                }

                return [
                    'sku' => (string) $saldo->bateria?->sku,
                    'marca' => (string) $saldo->bateria?->marca,
                    'deposito' => (string) $saldo->deposito?->nome,
                    'dias_em_estoque' => $diasEmEstoque,
                ];
            })
            ->filter()
            ->values();

        return view('livewire.estoque-dashboard', [
            'saldos' => $saldos,
            'saldoTotal' => (int) $saldos->sum('quantidade_atual'),
            'produtosEmAlerta' => (int) $saldos->where('quantidade_atual', '<=', 5)->count(),
            'valorTotalEstoque' => (float) $saldos->sum(fn (EstoqueSaldo $saldo): float => (float) ($saldo->bateria?->preco_venda ?? 0) * (int) $saldo->quantidade_atual),
            'maisVendidos' => $maisVendidos,
            'limiteShelfLife' => $limiteShelfLife,
            'alertasShelfLife' => $alertasShelfLife,
            'saidasPorPeriodo' => [
                'labels' => $periodoSaidas->map(fn ($data): string => $data->translatedFormat('d M'))->all(),
                'valores' => $periodoSaidas->map(fn ($data): int => (int) $movimentacoesSaida->get($data->format('Y-m-d'), collect())->sum('quantidade'))->all(),
            ],
        ]);
    }
}
