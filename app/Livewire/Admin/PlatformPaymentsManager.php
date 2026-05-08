<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\CobrancaSaaSExterna;
use App\Models\FaturaSaaS;
use App\Services\Billing\ExternalChargeIssuanceService;
use App\Services\Billing\GatewayRegistryService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PlatformPaymentsManager extends Component
{
    public string $faturaSaaSId = '';

    public string $gatewayCobrancaSaaSId = '';

    public string $paymentChannel = 'boleto';

    public bool $forceReissue = false;

    public string $reason = '';

    public function save(
        ExternalChargeIssuanceService $externalChargeIssuanceService,
        GatewayRegistryService $gatewayRegistryService,
    ): void {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-payments');

        $validated = $this->validate(
            $this->rules(),
            $this->messages(),
        );

        $externalChargeIssuanceService->issue(
            faturaSaaS: FaturaSaaS::query()->findOrFail((int) $validated['faturaSaaSId']),
            gatewayCobrancaSaaS: $gatewayRegistryService->findActive((int) $validated['gatewayCobrancaSaaSId']),
            paymentChannel: $validated['paymentChannel'],
            actor: auth('platform')->user(),
            forceReissue: $validated['forceReissue'],
            reason: $validated['reason'] ?: 'Cobranca SaaS emitida pelo painel central.',
        );

        $this->resetForm();
        session()->flash('status', 'Cobranca SaaS emitida com sucesso.');
    }

    public function render()
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-payments');

        return view('livewire.admin.platform-payments-manager', [
            'faturas' => FaturaSaaS::query()
                ->with(['cliente', 'assinatura', 'cobrancasExternas'])
                ->whereIn('status', ['pending', 'overdue'])
                ->orderBy('vencimento')
                ->limit(20)
                ->get(),
            'gateways' => app(GatewayRegistryService::class)->activeGateways(),
            'charges' => CobrancaSaaSExterna::query()
                ->with(['fatura.cliente', 'gateway'])
                ->latest()
                ->limit(15)
                ->get(),
        ]);
    }

    public function rules(): array
    {
        return [
            'faturaSaaSId' => ['required', 'integer', Rule::exists(FaturaSaaS::class, 'id')],
            'gatewayCobrancaSaaSId' => ['required', 'integer'],
            'paymentChannel' => ['required', Rule::in(['boleto', 'pix'])],
            'forceReissue' => ['boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'faturaSaaSId.required' => 'Selecione a fatura SaaS.',
            'gatewayCobrancaSaaSId.required' => 'Selecione o gateway de cobrança.',
            'paymentChannel.required' => 'Selecione o meio de pagamento.',
        ];
    }

    private function resetForm(): void
    {
        $this->faturaSaaSId = '';
        $this->gatewayCobrancaSaaSId = '';
        $this->paymentChannel = 'boleto';
        $this->forceReissue = false;
        $this->reason = '';
    }
}
