<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\BateriaEmprestimo;
use App\Models\Cliente;
use App\Models\OrdemServicoGarantia;
use App\Models\Vale;
use App\Services\LoanBatteryTermService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class GarantiaForm extends Component
{
    public ?int $clienteId = null;

    public ?int $bateriaId = null;

    public ?int $valeOriginalId = null;

    public ?int $bateriaEmprestimoId = null;

    public string $dataDevolucaoPrevista = '';

    public ?int $garantiaId = null;

    public function mount(): void
    {
        Gate::authorize('acesso-tecnico');
        $this->dataDevolucaoPrevista = now()->addDays(7)->format('Y-m-d\TH:i');
    }

    public function openGuarantee(): void
    {
        $validated = $this->validate([
            'clienteId' => ['required', 'exists:clientes,id'],
            'bateriaId' => ['required', 'exists:baterias,id'],
            'valeOriginalId' => ['nullable', 'exists:vales,id'],
        ]);

        $garantia = OrdemServicoGarantia::query()->create([
            'cliente_id' => $validated['clienteId'],
            'bateria_id' => $validated['bateriaId'],
            'vale_original_id' => $validated['valeOriginalId'],
            'data_abertura' => now(),
            'status' => 'aberta',
        ]);

        $this->garantiaId = $garantia->id;
    }

    public function checkoutLoanBattery(LoanBatteryTermService $loanBatteryTermService): void
    {
        if (! $this->garantiaId) {
            $this->addError('garantiaId', 'Abra a OS de garantia antes de liberar emprestimo.');

            return;
        }

        $validated = $this->validate([
            'bateriaEmprestimoId' => ['required', 'exists:baterias,id'],
            'dataDevolucaoPrevista' => ['required', 'date'],
        ]);

        $emprestimo = BateriaEmprestimo::query()->create([
            'os_garantia_id' => $this->garantiaId,
            'bateria_usada_id' => $validated['bateriaEmprestimoId'],
            'data_retirada' => now(),
            'data_devolucao_prevista' => $validated['dataDevolucaoPrevista'],
            'termo_arquivo_path' => null,
        ]);

        $emprestimo->update([
            'termo_arquivo_path' => 'generated://loan-term/'.md5($loanBatteryTermService->generate($emprestimo)),
        ]);

        $this->dispatch('garantia-updated');
    }

    public function render()
    {
        return view('livewire.garantia-form', [
            'clientes' => Cliente::query()->orderBy('razao_social')->get(),
            'baterias' => Bateria::query()->orderBy('sku')->get(),
            'vales' => Vale::query()->orderByDesc('id')->limit(20)->get(),
        ]);
    }
}
