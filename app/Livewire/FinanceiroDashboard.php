<?php

namespace App\Livewire;

use App\Models\ContaBancaria;
use App\Models\TransacaoFinanceira;
use App\Models\Filial;
use App\Services\FinanceService;
use App\Services\BankConciliationService;
use Livewire\Component;
use Carbon\Carbon;

class FinanceiroDashboard extends Component
{
    public $filialId;
    public $selectedContaId;
    public $resumoFluxo = [];
    public $conciliacaoResult = null;

    public function mount()
    {
        $this->filialId = auth()->user()->filial_id ?? session('filial_id');
        $conta = ContaBancaria::where('filial_id', $this->filialId)->first();
        if ($conta) {
            $this->selectedContaId = $conta->id;
        }
        $this->carregarFluxo();
    }

    public function carregarFluxo()
    {
        $service = new FinanceService();
        $this->resumoFluxo = $service->projetarSaldoDiario($this->filialId, now()->addDays(7));
    }

    public function rodarConciliacao()
    {
        if (!$this->selectedContaId) return;

        $conta = ContaBancaria::find($this->selectedContaId);
        $service = new BankConciliationService();
        $this->conciliacaoResult = $service->conciliarConta($conta);
        
        $this->carregarFluxo();
        session()->flash('success', 'Conciliação automática finalizada!');
    }

    public function render()
    {
        $contas = ContaBancaria::where('filial_id', $this->filialId)->get();
        $transacoes = TransacaoFinanceira::where('conta_id', $this->selectedContaId)
            ->latest('data')
            ->take(10)
            ->get();

        return view('livewire.financeiro-dashboard', [
            'contas' => $contas,
            'recentes' => $transacoes
        ]);
    }
}
