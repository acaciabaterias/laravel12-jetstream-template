<?php

namespace App\Livewire;

use App\Models\GeolocalizacaoEvento;
use App\Models\PontoEntrega;
use App\Models\RecebimentoMovel;
use App\Models\RotaEntrega;
use App\Services\DeliverySyncService;
use App\Services\RouteCloseValidator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DeliveryRouteScreen extends Component
{
    public ?int $rotaEntregaId = null;

    public ?int $pontoEntregaId = null;

    public string $metodoPagamento = 'pix';

    public string $valorRecebido = '0';

    public string $pesoSucataColetado = '0';

    public string $observacao = '';

    public string $dispositivoUuid = 'device-local';

    public function mount(): void
    {
        Gate::authorize('acesso-logistica');
    }

    public function registerPayment(): void
    {
        Gate::authorize('acesso-logistica');

        $validated = $this->validate([
            'pontoEntregaId' => ['required', 'exists:pontos_entrega,id'],
            'metodoPagamento' => ['required', 'in:pix,cartao,dinheiro'],
            'valorRecebido' => ['required', 'numeric', 'min:0.01'],
        ]);

        RecebimentoMovel::query()->create([
            'ponto_entrega_id' => $validated['pontoEntregaId'],
            'valor' => $validated['valorRecebido'],
            'metodo_pagamento' => $validated['metodoPagamento'],
            'status_sincronizado' => false,
            'comprovante_path' => null,
        ]);

        app(DeliverySyncService::class)->sync([
            'dispositivo_uuid' => $this->dispositivoUuid,
            'entidade_tipo' => RecebimentoMovel::class,
            'ponto_entrega_id' => $validated['pontoEntregaId'],
            'valor' => $validated['valorRecebido'],
            'metodo_pagamento' => $validated['metodoPagamento'],
        ]);
    }

    public function updateStop(): void
    {
        Gate::authorize('acesso-logistica');

        $validated = $this->validate([
            'pontoEntregaId' => ['required', 'exists:pontos_entrega,id'],
            'pesoSucataColetado' => ['nullable', 'numeric', 'min:0'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ]);

        $ponto = PontoEntrega::query()->findOrFail($validated['pontoEntregaId']);
        $ponto->update([
            'peso_sucata_coletado' => $validated['pesoSucataColetado'],
            'status' => 'concluido',
            'observacao' => $validated['observacao'],
        ]);

        app(DeliverySyncService::class)->sync([
            'dispositivo_uuid' => $this->dispositivoUuid,
            'entidade_tipo' => PontoEntrega::class,
            'entidade_id' => $ponto->id,
            'peso_sucata_coletado' => $validated['pesoSucataColetado'],
            'status' => 'concluido',
            'observacao' => $validated['observacao'],
        ]);
    }

    public function registerGeoEvent(): void
    {
        Gate::authorize('acesso-logistica');

        if (! $this->rotaEntregaId) {
            throw ValidationException::withMessages([
                'rotaEntregaId' => 'Selecione uma rota para registrar geolocalizacao.',
            ]);
        }

        app(DeliverySyncService::class)->sync([
            'dispositivo_uuid' => $this->dispositivoUuid,
            'entidade_tipo' => GeolocalizacaoEvento::class,
            'rota_entrega_id' => $this->rotaEntregaId,
            'ponto_entrega_id' => $this->pontoEntregaId,
            'latitude' => -23.55052,
            'longitude' => -46.63331,
            'tipo_evento' => 'checkin',
            'recorded_at' => now()->toDateTimeString(),
        ]);
    }

    public function closeRoute(RouteCloseValidator $routeCloseValidator): void
    {
        Gate::authorize('acesso-logistica');

        $rota = RotaEntrega::query()->with('pontos.recebimentos')->findOrFail($this->rotaEntregaId);
        $routeCloseValidator->assertCanClose($rota);
        $rota->update(['status' => 'encerrada']);
    }

    public function render()
    {
        return view('livewire.delivery-route-screen', [
            'rotas' => RotaEntrega::query()->with('pontos.cliente')->latest('id')->limit(6)->get(),
            'pontos' => $this->rotaEntregaId
                ? PontoEntrega::query()->where('rota_entrega_id', $this->rotaEntregaId)->orderBy('ordem_parada')->get()
                : collect(),
        ]);
    }
}
