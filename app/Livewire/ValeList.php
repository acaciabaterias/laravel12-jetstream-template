<?php

namespace App\Livewire;

use App\Jobs\ConvertValeToPedidoJob;
use App\Models\Vale;
use App\Services\ReservaEstoqueService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class ValeList extends Component
{
    public string $status = '';

    public string $search = '';

    public string $periodo = '30_dias';

    protected $listeners = ['vale-updated' => '$refresh'];

    public function mount(): void
    {
        Gate::authorize('acesso-vendas');
    }

    public function viewVale(int $valeId): void
    {
        $this->dispatch('vale-selected', valeId: $valeId);
    }

    public function cancelVale(int $valeId, ReservaEstoqueService $reservaEstoqueService): void
    {
        Gate::authorize('acesso-vendas');

        $vale = Vale::query()->findOrFail($valeId);

        if ($vale->status !== 'aberto') {
            throw ValidationException::withMessages([
                'vale' => 'Apenas vales abertos podem ser cancelados.',
            ]);
        }

        $reservaEstoqueService->estornarPorVale($vale);

        $vale->update([
            'status' => 'cancelado',
        ]);

        session()->flash('vale-feedback', "Vale #{$vale->id} cancelado com sucesso.");
        $this->dispatch('vale-updated');
    }

    public function faturarVale(int $valeId): void
    {
        Gate::authorize('acesso-vendas');

        ConvertValeToPedidoJob::dispatchSync($valeId, auth()->id());

        session()->flash('vale-feedback', "Vale #{$valeId} faturado com sucesso.");
        $this->dispatch('vale-updated');
        $this->dispatch('vale-selected', valeId: $valeId);
    }

    public function render(): View
    {
        $isMobile = str_contains(strtolower((string) request()->userAgent()), 'mobile');
        $cacheKey = sprintf(
            'vale-list:%s:%s:%s:%s',
            auth()->id() ?? 'guest',
            $this->status,
            $this->periodo,
            md5($this->search)
        );

        $vales = $isMobile
            ? Cache::remember($cacheKey, 60, fn (): Collection => $this->queryVales())
            : $this->queryVales();

        return view('livewire.vale-list', [
            'vales' => $vales,
        ]);
    }

    protected function queryVales(): Collection
    {
        $vales = Vale::query()
            ->with(['cliente', 'vendedor', 'itens'])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->periodo !== '', function ($query): void {
                $inicio = match ($this->periodo) {
                    'hoje' => now()->startOfDay(),
                    '7_dias' => now()->subDays(7)->startOfDay(),
                    '30_dias' => now()->subDays(30)->startOfDay(),
                    '90_dias' => now()->subDays(90)->startOfDay(),
                    default => null,
                };

                if ($inicio) {
                    $query->where('data_criacao', '>=', $inicio);
                }
            })
            ->when($this->search !== '', function ($query): void {
                $query->whereHas('cliente', function ($clienteQuery): void {
                    $clienteQuery->where('razao_social', 'like', '%'.$this->search.'%')
                        ->orWhere('nome_fantasia', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('id')
            ->limit(12)
            ->get();

        return $vales;
    }
}
