<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\MargemLucroReal;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MarginAnalysisGrid extends Component
{
    public ?int $bateriaId = null;

    public string $custoAquisicao = '0';

    public string $frete = '0';

    public string $imposto = '0';

    public string $comissao = '0';

    public function mount(): void
    {
        Gate::authorize('acesso-financeiro');
    }

    public function calculate(): void
    {
        Gate::authorize('acesso-financeiro');

        $validated = $this->validate([
            'bateriaId' => ['required', 'exists:baterias,id'],
            'custoAquisicao' => ['required', 'numeric', 'min:0'],
            'frete' => ['required', 'numeric', 'min:0'],
            'imposto' => ['required', 'numeric', 'min:0'],
            'comissao' => ['required', 'numeric', 'min:0'],
        ]);

        $bateria = Bateria::query()->findOrFail($validated['bateriaId']);
        $valorVenda = (float) ($bateria->preco_venda ?? 0);
        $custos = (float) $validated['custoAquisicao'] + (float) $validated['frete'] + (float) $validated['imposto'] + (float) $validated['comissao'];
        $margem = $valorVenda > 0 ? round(($valorVenda - $custos) / $valorVenda, 4) : 0;

        MargemLucroReal::query()->updateOrCreate(
            [
                'bateria_id' => $bateria->id,
                'periodo_inicio' => now()->startOfMonth()->toDateString(),
                'periodo_fim' => now()->endOfMonth()->toDateString(),
            ],
            [
                'valor_venda' => $valorVenda,
                'custo_aquisicao' => $validated['custoAquisicao'],
                'frete' => $validated['frete'],
                'imposto' => $validated['imposto'],
                'comissao' => $validated['comissao'],
                'margem_calculada' => $margem,
            ],
        );
    }

    public function render()
    {
        return view('livewire.margin-analysis-grid', [
            'baterias' => Bateria::query()->orderBy('sku')->get(),
            'margens' => MargemLucroReal::query()->latest('id')->limit(8)->get(),
        ]);
    }
}
