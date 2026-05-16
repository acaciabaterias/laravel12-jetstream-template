<?php

namespace App\Livewire;

use App\Jobs\SendGuaranteeWhatsAppNotificationJob;
use App\Models\NotificacaoWhatsApp;
use App\Models\OrdemServicoGarantia;
use App\Services\GuaranteeChargeService;
use App\Services\ReturnIndexService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class GarantiaLaudoForm extends Component
{
    public ?int $garantiaId = null;

    public string $laudo = '';

    public string $resultado = 'procedente';

    public string $cobrancaValor = '0';

    public function loadGuarantee(int $garantiaId): void
    {
        Gate::authorize('acesso-tecnico');

        $garantia = OrdemServicoGarantia::query()->findOrFail($garantiaId);
        $this->garantiaId = $garantia->id;
        $this->laudo = $garantia->laudo ?? '';
        $this->resultado = $garantia->resultado ?? 'procedente';
        $this->cobrancaValor = (string) ($garantia->cobranca_valor ?? '0');
    }

    public function save(GuaranteeChargeService $guaranteeChargeService, ReturnIndexService $returnIndexService): void
    {
        Gate::authorize('acesso-tecnico');

        $validated = $this->validate([
            'garantiaId' => ['required', 'exists:ordens_servico_garantia,id'],
            'laudo' => ['required', 'string', 'max:2000'],
            'resultado' => ['required', 'in:procedente,improcedente'],
            'cobrancaValor' => ['nullable', 'numeric', 'min:0'],
        ]);

        $garantia = OrdemServicoGarantia::query()->with('cliente')->findOrFail($validated['garantiaId']);
        $garantia->update([
            'laudo' => $validated['laudo'],
            'resultado' => $validated['resultado'],
            'status' => $validated['resultado'] === 'improcedente' ? 'aguardando_pagamento' : 'concluida',
        ]);

        if ($validated['resultado'] === 'improcedente') {
            $guaranteeChargeService->generateImprocedenteCharge($garantia, (float) $validated['cobrancaValor']);
        }

        $returnIndexService->refreshForBattery($garantia->bateria_id);

        $notificacao = NotificacaoWhatsApp::query()->create([
            'os_garantia_id' => $garantia->id,
            'cliente_telefone' => $garantia->cliente->telefone ?? null,
            'status' => 'pendente',
            'mensagem' => 'Sua OS de garantia foi atualizada para '.$garantia->status.'.',
        ]);

        SendGuaranteeWhatsAppNotificationJob::dispatchSync($notificacao->id);
        $this->dispatch('garantia-updated');
    }

    public function render()
    {
        return view('livewire.garantia-laudo-form', [
            'garantias' => OrdemServicoGarantia::query()->latest('id')->limit(8)->get(),
        ]);
    }
}
