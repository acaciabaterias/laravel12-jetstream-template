<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\Deposito;
use App\Models\EstoqueMovimentacao;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Exception;

class EstoqueAjuste extends Component
{
    public $bateria_id;
    public $deposito_id;
    public $tipo = 'entrada'; // entrada, saida
    public $quantidade = 1;
    public $justificativa = '';

    public $searchBateria = '';
    public $bateriasResults = [];
    public $bateriaSelecionada = null;

    protected $rules = [
        'deposito_id' => 'required|exists:depositos,id',
        'tipo' => 'required|in:entrada,saida',
        'quantidade' => 'required|integer|min:1',
        'justificativa' => 'required|string|min:5|max:255',
    ];

    public function updatedSearchBateria()
    {
        if (strlen($this->searchBateria) >= 2) {
            $this->bateriasResults = Bateria::where('sku', 'like', '%' . $this->searchBateria . '%')
                ->orWhere('marca', 'like', '%' . $this->searchBateria . '%')
                ->take(5)->get();
        } else {
            $this->bateriasResults = [];
        }
    }

    public function selectBateria($id)
    {
        $this->bateriaSelecionada = Bateria::find($id);
        $this->bateria_id = $id;
        $this->bateriasResults = [];
        $this->searchBateria = $this->bateriaSelecionada->sku . ' - ' . $this->bateriaSelecionada->marca;
    }

    public function save()
    {
        $this->validate();

        if (!$this->bateriaSelecionada) {
            $this->addError('bateria_id', 'Selecione uma bateria.');
            return;
        }

        try {
            DB::transaction(function () {
                EstoqueMovimentacao::create([
                    'bateria_id' => $this->bateria_id,
                    'filial_id' => auth()->user()->filial_id ?? session('filial_id'),
                    'deposito_id' => $this->deposito_id,
                    'user_id' => auth()->id(),
                    'tipo' => $this->tipo,
                    'quantidade' => $this->quantidade,
                    'origem' => 'Ajuste Manual',
                    'referencia' => $this->justificativa,
                ]);
            });

            session()->flash('message', 'Estoque ajustado com sucesso.');
            $this->reset(['quantidade', 'justificativa', 'searchBateria', 'bateriaSelecionada', 'bateria_id']);
            
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.estoque-ajuste', [
            'depositos' => Deposito::all(),
        ]);
    }
}
