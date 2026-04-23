<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\PontoEntrega;
use App\Models\RotaEntrega;
use App\Models\User;
use App\Models\Vale;
use App\Models\Veiculo;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class RoutePlanner extends Component
{
    public ?int $rotaEntregaId = null;

    public ?int $entregadorId = null;

    public ?int $veiculoId = null;

    public ?int $clienteId = null;

    public ?int $valeId = null;

    public string $enderecoEntrega = '';

    public string $observacoes = '';

    public function mount(): void
    {
        Gate::authorize('acesso-logistica');
    }

    public function createRoute(): void
    {
        Gate::authorize('acesso-logistica');

        $validated = $this->validate([
            'entregadorId' => ['required', 'exists:users,id'],
            'veiculoId' => ['nullable', 'exists:veiculos,id'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        $rota = RotaEntrega::query()->create([
            'entregador_id' => $validated['entregadorId'],
            'data_rota' => now()->toDateString(),
            'status' => 'planejada',
            'veiculo_id' => $validated['veiculoId'],
            'observacoes' => $validated['observacoes'],
        ]);

        $this->rotaEntregaId = $rota->id;
    }

    public function addStop(): void
    {
        Gate::authorize('acesso-logistica');

        $validated = $this->validate([
            'rotaEntregaId' => ['required', 'exists:rotas_entrega,id'],
            'clienteId' => ['required', 'exists:clientes,id'],
            'valeId' => ['nullable', 'exists:vales,id'],
            'enderecoEntrega' => ['required', 'string', 'max:255'],
        ]);

        $rota = RotaEntrega::query()->withCount('pontos')->findOrFail($validated['rotaEntregaId']);

        PontoEntrega::query()->create([
            'rota_entrega_id' => $rota->id,
            'vale_id' => $validated['valeId'],
            'cliente_id' => $validated['clienteId'],
            'endereco_entrega' => $validated['enderecoEntrega'],
            'ordem_parada' => $rota->pontos_count + 1,
            'status' => 'planejado',
        ]);

        $this->reset(['clienteId', 'valeId', 'enderecoEntrega']);
        $this->dispatch('route-updated');
    }

    public function render()
    {
        $rota = $this->rotaEntregaId
            ? RotaEntrega::query()->with(['pontos.cliente', 'entregador'])->find($this->rotaEntregaId)
            : null;

        return view('livewire.route-planner', [
            'entregadores' => User::query()->where('papel', 'entregador')->orderBy('name')->get(),
            'veiculos' => Veiculo::query()->orderBy('modelo')->get(),
            'clientes' => Cliente::query()->orderBy('razao_social')->get(),
            'vales' => Vale::query()->where('status', 'aberto')->orderByDesc('id')->get(),
            'rota' => $rota,
        ]);
    }
}
