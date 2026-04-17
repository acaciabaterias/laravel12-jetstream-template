<?php

namespace App\Livewire;

use App\Models\Bateria;
use Livewire\Component;
use Livewire\WithPagination;

class BateriaManager extends Component
{
    use WithPagination;

    public $sku, $marca, $tecnologia, $amperagem, $polo, $preco_venda;
    public $peso_sucata_kg, $valor_base_sucata_kg, $tem_logistica_reversa = true;
    public $atributos_dinamicos = '';

    public $bateriaId;
    public $isEditMode = false;
    public $showModal = false;
    public $search = '';

    protected $rules = [
        'sku' => 'required|string|max:50',
        'marca' => 'required|string|max:100',
        'tecnologia' => 'nullable|string|max:50',
        'amperagem' => 'nullable|integer',
        'polo' => 'nullable|string|max:20',
        'preco_venda' => 'required|numeric|min:0',
        'peso_sucata_kg' => 'nullable|numeric|min:0',
        'valor_base_sucata_kg' => 'nullable|numeric|min:0',
        'tem_logistica_reversa' => 'boolean',
        'atributos_dinamicos' => 'nullable|string', // Validated as json string for now
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $bateria = Bateria::withTrashed()->findOrFail($id);
        $this->bateriaId = $bateria->id;
        $this->sku = $bateria->sku;
        $this->marca = $bateria->marca;
        $this->tecnologia = $bateria->tecnologia;
        $this->amperagem = $bateria->amperagem;
        $this->polo = $bateria->polo;
        $this->preco_venda = $bateria->preco_venda;
        $this->peso_sucata_kg = $bateria->peso_sucata_kg;
        $this->valor_base_sucata_kg = $bateria->valor_base_sucata_kg;
        $this->tem_logistica_reversa = $bateria->tem_logistica_reversa;
        $this->atributos_dinamicos = $bateria->atributos_dinamicos ? json_encode($bateria->atributos_dinamicos, JSON_PRETTY_PRINT) : '';

        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        $atributos = null;
        if (!empty($this->atributos_dinamicos)) {
            $atributos = json_decode($this->atributos_dinamicos, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('atributos_dinamicos', 'Formato JSON inválido.');
                return;
            }
        }

        $data = [
            'sku' => $this->sku,
            'marca' => $this->marca,
            'tecnologia' => $this->tecnologia,
            'amperagem' => $this->amperagem,
            'polo' => $this->polo,
            'preco_venda' => $this->preco_venda,
            'peso_sucata_kg' => $this->peso_sucata_kg,
            'valor_base_sucata_kg' => $this->valor_base_sucata_kg,
            'tem_logistica_reversa' => $this->tem_logistica_reversa,
            'atributos_dinamicos' => $atributos,
            'filial_id' => auth()->user()->filial_id ?? session('filial_id'),
        ];

        if ($this->isEditMode) {
            $bateria = Bateria::withTrashed()->findOrFail($this->bateriaId);
            $bateria->update($data);
        } else {
            Bateria::create($data);
        }

        $this->showModal = false;
        $this->resetInputFields();
    }

    public function toggleStatus($id)
    {
        $bateria = Bateria::withTrashed()->findOrFail($id);
        if ($bateria->trashed()) {
            $bateria->restore();
        } else {
            $bateria->delete();
        }
    }

    private function resetInputFields()
    {
        $this->sku = '';
        $this->marca = '';
        $this->tecnologia = '';
        $this->amperagem = null;
        $this->polo = '';
        $this->preco_venda = 0;
        $this->peso_sucata_kg = null;
        $this->valor_base_sucata_kg = null;
        $this->tem_logistica_reversa = true;
        $this->atributos_dinamicos = '';
        $this->bateriaId = null;
    }

    public function render()
    {
        $query = Bateria::withTrashed();
        
        if (!empty($this->search)) {
            $query->where('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('marca', 'like', '%' . $this->search . '%');
        }

        return view('livewire.bateria-manager', [
            'baterias' => $query->latest()->paginate(10),
        ]);
    }
}
