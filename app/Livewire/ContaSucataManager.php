<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Fornecedor;
use Livewire\Component;

class ContaSucataManager extends Component
{
    public $viewType = 'fornecedores'; // fornecedores ou clientes

    public function render()
    {
        $fornecedores = [];
        $clientes = [];

        if ($this->viewType === 'fornecedores') {
            // Find fornecedores with active scrap accounts (saldo != 0)
            $fornecedores = Fornecedor::where('saldo_sucata_kg', '!=', 0)
                ->orderBy('nome')
                ->get();
        } else {
            $clientes = Cliente::where('saldo_sucata_kg', '!=', 0)
                ->orderBy('nome')
                ->get();
        }

        return view('livewire.conta-sucata-manager', [
            'fornecedores' => $fornecedores,
            'clientes' => $clientes,
        ]);
    }
}
