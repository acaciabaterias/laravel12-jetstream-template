<?php

namespace App\Livewire;

use App\Models\PontoEntrega;
use App\Models\RotaEntrega;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RotaBuilder extends Component
{
    public $entregadorId;
    public $dataRota;
    public $veiculo;
    public $observacoes;

    public $valesDisponiveis = [];
    public $valeIdsSelecionados = [];

    public function mount()
    {
        $this->dataRota = now()->format('Y-m-d');
        $this->carregarValesDisponiveis();
    }

    public function carregarValesDisponiveis()
    {
        // Vales que estão 'abertos' ou 'em_os' e que ainda não estão em nenhuma rota 'ativa' ou 'rascunho'
        $pontosEmRotasAtivas = PontoEntrega::whereHas('rotaEntrega', function ($query) {
            $query->whereIn('status', ['rascunho', 'ativa']);
        })->pluck('vale_id')->toArray();

        $this->valesDisponiveis = Vale::with('cliente')
            ->whereIn('status', ['aberto', 'em_os'])
            ->whereNotIn('id', $pontosEmRotasAtivas)
            ->latest()
            ->get();
    }

    public function toggleVale($valeId)
    {
        if (in_array($valeId, $this->valeIdsSelecionados)) {
            $this->valeIdsSelecionados = array_diff($this->valeIdsSelecionados, [$valeId]);
        } else {
            $this->valeIdsSelecionados[] = $valeId;
        }
    }

    public function criarRota()
    {
        $this->validate([
            'entregadorId' => 'required|exists:users,id',
            'dataRota' => 'required|date',
            'valeIdsSelecionados' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () {
                $rota = RotaEntrega::create([
                    'entregador_id' => $this->entregadorId,
                    'filial_id' => auth()->user()->filial_id ?? session('filial_id'),
                    'data_rota' => $this->dataRota,
                    'veiculo' => $this->veiculo,
                    'status' => 'rascunho',
                    'observacoes' => $this->observacoes,
                ]);

                foreach ($this->valeIdsSelecionados as $index => $valeId) {
                    PontoEntrega::create([
                        'rota_entrega_id' => $rota->id,
                        'vale_id' => $valeId,
                        'filial_id' => $rota->filial_id,
                        'ordem_parada' => $index + 1,
                        'status' => 'pendente',
                    ]);
                }
            });

            session()->flash('success', 'Rota criada com sucesso como Rascunho!');
            return redirect()->route('dashboard'); // Ou rota de listagem de rotas se existir

        } catch (\Exception $e) {
            $this->addError('geral', 'Falha ao criar rota: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.rota-builder', [
            'entregadores' => User::where('filial_id', auth()->user()->filial_id ?? session('filial_id'))->get(),
        ]);
    }
}
