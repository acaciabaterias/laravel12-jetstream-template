<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\CasoRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\PaymentPromiseService;
use App\Services\Billing\RevenueRecoveryEscalationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PlatformRevenueRecoveryManager extends Component
{
    public string $casoRecuperacaoReceitaId = '';

    public string $ownerUserId = '';

    public string $escalationReason = '';

    public string $promisedAmount = '';

    public string $promisedDate = '';

    public string $suspendsUntil = '';

    public string $promiseNotes = '';

    public function escalateCase(RevenueRecoveryEscalationService $revenueRecoveryEscalationService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-revenue-recovery');

        $validated = $this->validate($this->escalationRules(), $this->messages());

        $owner = filled($validated['ownerUserId'])
            ? UsuarioPlataforma::query()->findOrFail((int) $validated['ownerUserId'])
            : null;

        $revenueRecoveryEscalationService->escalate(
            casoRecuperacaoReceita: CasoRecuperacaoReceita::query()->findOrFail((int) $validated['casoRecuperacaoReceitaId']),
            owner: $owner,
            actor: auth('platform')->user(),
            reason: $validated['escalationReason'] ?: 'Escalonamento operacional manual.',
        );

        session()->flash('status', 'Caso escalado com sucesso.');
    }

    public function recordPromise(PaymentPromiseService $paymentPromiseService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-revenue-recovery');

        $validated = $this->validate($this->promiseRules(), $this->messages());

        $paymentPromiseService->record(
            casoRecuperacaoReceita: CasoRecuperacaoReceita::query()->findOrFail((int) $validated['casoRecuperacaoReceitaId']),
            actor: auth('platform')->user(),
            attributes: [
                'promised_amount' => $validated['promisedAmount'] !== '' ? $validated['promisedAmount'] : null,
                'promised_date' => $validated['promisedDate'],
                'suspends_until' => $validated['suspendsUntil'] ?: $validated['promisedDate'],
                'notes' => $validated['promiseNotes'] ?: null,
            ],
        );

        session()->flash('status', 'Promessa de pagamento registrada com sucesso.');
    }

    public function render()
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-revenue-recovery');

        return view('livewire.admin.platform-revenue-recovery-manager', [
            'casos' => CasoRecuperacaoReceita::query()
                ->with(['cliente', 'fatura', 'owner'])
                ->whereIn('status', ['open', 'paused', 'escalated'])
                ->latest()
                ->limit(20)
                ->get(),
            'owners' => UsuarioPlataforma::query()
                ->where('ativo', true)
                ->whereIn('papel', ['super_admin', 'billing'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function escalationRules(): array
    {
        return [
            'casoRecuperacaoReceitaId' => ['required', 'integer', Rule::exists(CasoRecuperacaoReceita::class, 'id')],
            'ownerUserId' => ['nullable', 'integer', Rule::exists(UsuarioPlataforma::class, 'id')],
            'escalationReason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function promiseRules(): array
    {
        return [
            'casoRecuperacaoReceitaId' => ['required', 'integer', Rule::exists(CasoRecuperacaoReceita::class, 'id')],
            'promisedAmount' => ['nullable', 'numeric', 'min:0'],
            'promisedDate' => ['required', 'date'],
            'suspendsUntil' => ['nullable', 'date'],
            'promiseNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'casoRecuperacaoReceitaId.required' => 'Selecione o caso de recuperação.',
            'promisedDate.required' => 'Informe a data prometida.',
        ];
    }
}
