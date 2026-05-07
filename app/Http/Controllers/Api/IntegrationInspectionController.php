<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntegrationInspectionRequest;
use App\Http\Requests\ReplayIntegrationEventRequest;
use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Services\Contracts\Integration\IntegrationReplayServiceContract;
use App\Services\Integration\EventContractCatalogService;
use App\Services\Integration\IntegrationGatewayRegistry;
use App\Services\Integration\IntegrationMetrics;
use Illuminate\Http\JsonResponse;

class IntegrationInspectionController extends Controller
{
    public function index(
        IntegrationInspectionRequest $request,
        EventContractCatalogService $contractCatalogService,
        IntegrationGatewayRegistry $gatewayRegistry,
        IntegrationMetrics $metrics,
    ): JsonResponse {
        abort_unless(
            $request->user() && ($request->user()->isSuperAdmin() || $request->user()->hasRole(['dono', 'gestor'])),
            403
        );

        $validated = $request->validated();
        $limit = (int) ($validated['limit'] ?? 25);
        $snapshot = $metrics->syncOperationalSnapshot();

        $outboxes = EventoOutbox::query()
            ->when(isset($validated['event_type']), fn ($query) => $query->where('event_type', $validated['event_type']))
            ->when(isset($validated['tenant_external_ref']), fn ($query) => $query->where('tenant_external_ref', $validated['tenant_external_ref']))
            ->latest('id')
            ->limit($limit)
            ->get();

        $deliveries = EntregaIntegracao::query()
            ->with('entregavel')
            ->when(isset($validated['status']), fn ($query) => $query->where('status', $validated['status']))
            ->when(isset($validated['direction']), fn ($query) => $query->where('direction', $validated['direction']))
            ->when(isset($validated['target']), fn ($query) => $query->where('target', $validated['target']))
            ->when(isset($validated['from']), fn ($query) => $query->where('created_at', '>=', $validated['from']))
            ->when(isset($validated['to']), fn ($query) => $query->where('created_at', '<=', $validated['to']))
            ->latest('id')
            ->limit($limit)
            ->get();

        return response()->json([
            'summary' => [
                'outboxes' => $outboxes->count(),
                'deliveries' => $deliveries->count(),
                'contracts' => $contractCatalogService->list($validated['event_type'] ?? null)->count(),
                'endpoints' => $gatewayRegistry->list()->count(),
            ],
            'metrics' => $snapshot,
            'outboxes' => $outboxes->map(fn (EventoOutbox $outbox): array => [
                'id' => $outbox->id,
                'event_type' => $outbox->event_type,
                'event_version' => $outbox->event_version,
                'tenant_external_ref' => $outbox->tenant_external_ref,
                'status' => $outbox->status->value,
                'idempotency_key' => $outbox->idempotency_key,
            ])->all(),
            'deliveries' => $deliveries->map(fn (EntregaIntegracao $delivery): array => [
                'id' => $delivery->id,
                'direction' => $delivery->direction->value,
                'target' => $delivery->target,
                'status' => $delivery->status->value,
                'attempt_number' => $delivery->attempt_number,
            ])->all(),
            'contracts' => $contractCatalogService->list($validated['event_type'] ?? null)
                ->map(fn ($contract): array => [
                    'event_type' => $contract->event_type,
                    'event_version' => $contract->event_version,
                    'producer' => $contract->producer,
                    'consumers' => $contract->consumers,
                ])->all(),
            'endpoints' => $gatewayRegistry->list()
                ->map(fn ($endpoint): array => [
                    'service_name' => $endpoint->service_name,
                    'route_name' => $endpoint->route_name,
                    'method' => $endpoint->method,
                    'timeout_ms' => $endpoint->timeout_ms,
                ])->all(),
        ]);
    }

    public function replay(
        ReplayIntegrationEventRequest $request,
        IntegrationReplayServiceContract $replayService,
    ): JsonResponse {
        abort_unless(
            $request->user() && ($request->user()->isSuperAdmin() || $request->user()->hasRole(['dono', 'gestor'])),
            403
        );

        $delivery = EntregaIntegracao::query()->findOrFail($request->integer('delivery_id'));
        $replay = $replayService->replay($delivery, $request->user(), [
            'reason' => $request->string('reason')->toString(),
            'source' => 'http-api',
        ]);

        return response()->json([
            'status' => 'replayed',
            'delivery_id' => $delivery->id,
            'replay_id' => $replay->id,
        ]);
    }
}
