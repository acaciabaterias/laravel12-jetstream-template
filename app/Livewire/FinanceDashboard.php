<?php

namespace App\Livewire;

use App\Models\ConciliacaoPendente;
use App\Models\ContaBancaria;
use App\Models\TransacaoFinanceira;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class FinanceDashboard extends Component
{
    public function mount(): void
    {
        Gate::authorize('acesso-financeiro');
    }

    public function render(): View
    {
        $transacoesRecentes = TransacaoFinanceira::query()
            ->latest('data_transacao')
            ->limit(8)
            ->get();

        $transacoesFluxo = TransacaoFinanceira::query()
            ->where('data_transacao', '>=', now()->subDays(6)->startOfDay())
            ->orderBy('data_transacao')
            ->get()
            ->groupBy(fn (TransacaoFinanceira $transacao): string => $transacao->data_transacao->format('Y-m-d'));

        $periodoFluxo = collect(range(0, 6))
            ->map(fn (int $offset) => now()->subDays(6 - $offset)->startOfDay());

        $receitas = (float) TransacaoFinanceira::query()
            ->where('tipo', 'receita')
            ->sum('valor');

        $despesas = (float) TransacaoFinanceira::query()
            ->where('tipo', 'despesa')
            ->sum('valor');

        $margemMedia = $receitas > 0
            ? (($receitas - $despesas) / $receitas) * 100
            : 0.0;

        return view('livewire.finance-dashboard', [
            'contas' => ContaBancaria::query()->latest('id')->limit(5)->get(),
            'transacoesPendentes' => ConciliacaoPendente::query()->where('status', 'pendente')->count(),
            'transacoesRecentes' => $transacoesRecentes,
            'totalReceber' => $receitas,
            'totalPagar' => $despesas,
            'margemMedia' => $margemMedia,
            'fluxoCaixa' => [
                'labels' => $periodoFluxo->map(fn ($data): string => $data->translatedFormat('d M'))->all(),
                'entradas' => $periodoFluxo->map(fn ($data): float => $this->sumByType($transacoesFluxo->get($data->format('Y-m-d'), collect()), 'receita'))->all(),
                'saidas' => $periodoFluxo->map(fn ($data): float => $this->sumByType($transacoesFluxo->get($data->format('Y-m-d'), collect()), 'despesa'))->all(),
            ],
        ]);
    }

    protected function sumByType(Collection $transacoes, string $tipo): float
    {
        return round((float) $transacoes
            ->where('tipo', $tipo)
            ->sum('valor'), 2);
    }
}
