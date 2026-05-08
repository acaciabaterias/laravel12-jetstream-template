<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PlatformSubscriptionManager extends Component
{
    public string $clienteId = '';

    public string $planoId = '';

    public string $politicaInadimplenciaId = '';

    public string $status = 'active';

    public string $dataInicio = '';

    public string $dataProximoCiclo = '';

    public string $observacoes = '';

    public string $reason = '';

    public function save(SubscriptionLifecycleService $subscriptionLifecycleService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-billing');

        $validated = $this->validate(
            $this->rules(),
            $this->messages(),
        );

        $subscriptionLifecycleService->activate(
            cliente: Cliente::query()->findOrFail((int) $validated['clienteId']),
            planoComercial: PlanoComercial::query()->findOrFail((int) $validated['planoId']),
            politicaInadimplencia: filled($validated['politicaInadimplenciaId'] ?? null)
                ? PoliticaInadimplencia::query()->findOrFail((int) $validated['politicaInadimplenciaId'])
                : null,
            attributes: [
                'status' => $validated['status'],
                'data_inicio' => $validated['dataInicio'] ?: null,
                'data_proximo_ciclo' => $validated['dataProximoCiclo'] ?: null,
                'observacoes' => $validated['observacoes'] ?: null,
                'reason' => $validated['reason'] ?: 'Assinatura ativada pelo painel central.',
            ],
            actor: auth('platform')->user(),
        );

        $this->resetForm();
        session()->flash('status', 'Assinatura ativada com sucesso.');
    }

    public function render()
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-billing');

        return view('livewire.admin.platform-subscription-manager', [
            'clientes' => Cliente::query()->orderBy('razao_social')->get(),
            'planos' => PlanoComercial::query()->where('ativo', true)->orderBy('nome')->get(),
            'politicas' => PoliticaInadimplencia::query()->where('status', 'active')->orderBy('nome')->get(),
            'assinaturas' => AssinaturaPlataforma::query()
                ->with(['cliente', 'plano', 'politicaInadimplencia'])
                ->latest()
                ->limit(15)
                ->get(),
        ]);
    }

    public function rules(): array
    {
        return [
            'clienteId' => ['required', 'integer', Rule::exists(Cliente::class, 'id')],
            'planoId' => ['required', 'integer', Rule::exists(PlanoComercial::class, 'id')],
            'politicaInadimplenciaId' => ['nullable', 'integer', Rule::exists(PoliticaInadimplencia::class, 'id')],
            'status' => ['required', Rule::in(['trial', 'active'])],
            'dataInicio' => ['nullable', 'date'],
            'dataProximoCiclo' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clienteId.required' => 'Selecione o assinante.',
            'planoId.required' => 'Selecione o plano comercial.',
            'status.required' => 'Selecione o status inicial da assinatura.',
        ];
    }

    private function resetForm(): void
    {
        $this->clienteId = '';
        $this->planoId = '';
        $this->politicaInadimplenciaId = '';
        $this->status = 'active';
        $this->dataInicio = '';
        $this->dataProximoCiclo = '';
        $this->observacoes = '';
        $this->reason = '';
    }
}
