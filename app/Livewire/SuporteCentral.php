<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\ItemVale;
use App\Models\OrdemServicoGarantia;
use App\Models\Vale;
use App\Services\TraceabilityService;
use Livewire\Component;

class SuporteCentral extends Component
{
    public $search = '';
    public $results = null;
    public $clientHistory = null;
    public $selectedClientId = null;

    public function search()
    {
        $this->validate([
            'search' => 'required|min:3'
        ]);

        $service = new TraceabilityService();
        
        // 1. Tenta buscar por Numero de Série
        $snResult = $service->findBySerialNumber($this->search);
        
        if ($snResult['venda']) {
            $this->results = $snResult;
            $this->loadClientHistory($snResult['cliente']->id);
            return;
        }

        // 2. Se não achou SN, busca por Cliente
        $cliente = Cliente::where('razao_social', 'like', "%{$this->search}%")
            ->orWhere('cnpj', 'like', "%{$this->search}%")
            ->orWhere('nome_fantasia', 'like', "%{$this->search}%")
            ->first();

        if ($cliente) {
            $this->loadClientHistory($cliente->id);
            $this->results = ['cliente' => $cliente];
        } else {
            $this->results = 'empty';
            $this->clientHistory = null;
        }
    }

    protected function loadClientHistory($clienteId)
    {
        $this->selectedClientId = $clienteId;
        
        $vales = Vale::where('cliente_id', $clienteId)->with(['itens.bateria'])->latest()->get();
        $garantias = OrdemServicoGarantia::where('cliente_id', $clienteId)->with('bateria')->latest()->get();

        // Merging into a timeline
        $timeline = collect();

        foreach($vales as $v) {
            $timeline->push([
                'type' => 'venda',
                'date' => $v->created_at,
                'title' => "Compra de Bateria (Vale #{$v->id})",
                'description' => $v->itens->pluck('bateria.marca')->implode(', '),
                'status' => $v->status,
                'model' => $v
            ]);
        }

        foreach($garantias as $g) {
            $timeline->push([
                'type' => 'garantia',
                'date' => $g->created_at,
                'title' => "O.S. de Garantia #{$g->id}",
                'description' => "Produto: {$g->bateria->marca}. Laudo: " . ($g->resultado ?: 'Pendente'),
                'status' => $g->status,
                'model' => $g
            ]);
        }

        $this->clientHistory = $timeline->sortByDesc('date');
    }

    public function render()
    {
        return view('livewire.suporte-central');
    }
}
