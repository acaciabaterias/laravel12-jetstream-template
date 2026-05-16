<?php

namespace App\Services\Integration;

use App\Models\ContratoEvento;
use App\Models\EndpointIntegracao;
use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;

class IntegrationMetrics
{
    private static ?CollectorRegistry $fallbackRegistry = null;

    /**
     * @return array{
     *     outboxes: array<string, int>,
     *     deliveries: array<string, array<string, int>>,
     *     latency: array<string, array<string, float>>,
     *     contracts: array<string, int>,
     *     endpoints: array<string, array<string, int>>
     * }
     */
    public function syncOperationalSnapshot(): array
    {
        $snapshot = $this->snapshot();

        try {
            $registry = $this->registry();

            if (! $registry) {
                return $snapshot;
            }

            $outboxGauge = $registry->getOrRegisterGauge(
                'app',
                'integration_outbox_total',
                'Total de eventos na outbox por status',
                ['status']
            );

            foreach ($this->allFlowStatuses() as $status) {
                $outboxGauge->set($snapshot['outboxes'][$status] ?? 0, [$status]);
            }

            $deliveryGauge = $registry->getOrRegisterGauge(
                'app',
                'integration_deliveries_total',
                'Total de entregas de integracao por direcao e status',
                ['direction', 'status']
            );

            foreach ($this->allDirections() as $direction) {
                foreach ($this->allFlowStatuses() as $status) {
                    $deliveryGauge->set($snapshot['deliveries'][$direction][$status] ?? 0, [$direction, $status]);
                }
            }

            $latencyGauge = $registry->getOrRegisterGauge(
                'app',
                'integration_delivery_latency_average_ms',
                'Latencia media de entregas de integracao em milissegundos',
                ['direction', 'target']
            );

            foreach ($snapshot['latency'] as $direction => $targets) {
                foreach ($targets as $target => $value) {
                    $latencyGauge->set($value, [$direction, $target]);
                }
            }

            $contractsGauge = $registry->getOrRegisterGauge(
                'app',
                'integration_contracts_catalog_total',
                'Total de contratos de evento por status',
                ['status']
            );

            foreach ($this->knownContractStatuses() as $status) {
                $contractsGauge->set($snapshot['contracts'][$status] ?? 0, [$status]);
            }

            $endpointsGauge = $registry->getOrRegisterGauge(
                'app',
                'integration_gateway_endpoints_total',
                'Total de endpoints de integracao por servico e status',
                ['service_name', 'status']
            );

            foreach ($snapshot['endpoints'] as $serviceName => $statuses) {
                foreach ($statuses as $status => $count) {
                    $endpointsGauge->set($count, [$serviceName, $status]);
                }
            }
        } catch (\Throwable $exception) {
        }

        return $snapshot;
    }

    /**
     * @return array{
     *     outboxes: array<string, int>,
     *     deliveries: array<string, array<string, int>>,
     *     latency: array<string, array<string, float>>,
     *     contracts: array<string, int>,
     *     endpoints: array<string, array<string, int>>
     * }
     */
    public function snapshot(): array
    {
        if (! $this->hasBackboneTables()) {
            return [
                'outboxes' => [],
                'deliveries' => [],
                'latency' => [],
                'contracts' => [],
                'endpoints' => [],
            ];
        }

        $outboxes = EventoOutbox::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();

        $deliveries = EntregaIntegracao::query()
            ->selectRaw('direction, status, COUNT(*) as aggregate')
            ->groupBy('direction', 'status')
            ->get()
            ->groupBy('direction')
            ->map(function (Collection $rows): array {
                return $rows
                    ->mapWithKeys(fn (EntregaIntegracao $delivery): array => [
                        $delivery->status->value => (int) $delivery->getAttribute('aggregate'),
                    ])
                    ->all();
            })
            ->all();

        $latency = EntregaIntegracao::query()
            ->whereNotNull('latency_ms')
            ->selectRaw('direction, target, AVG(latency_ms) as average_latency_ms')
            ->groupBy('direction', 'target')
            ->get()
            ->groupBy('direction')
            ->map(function (Collection $rows): array {
                return $rows
                    ->mapWithKeys(fn (EntregaIntegracao $delivery): array => [
                        (string) $delivery->target => round((float) $delivery->getAttribute('average_latency_ms'), 2),
                    ])
                    ->all();
            })
            ->all();

        $contracts = ContratoEvento::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();

        $endpoints = EndpointIntegracao::query()
            ->selectRaw('service_name, status, COUNT(*) as aggregate')
            ->groupBy('service_name', 'status')
            ->get()
            ->groupBy('service_name')
            ->map(function (Collection $rows): array {
                return $rows
                    ->mapWithKeys(fn (EndpointIntegracao $endpoint): array => [
                        (string) $endpoint->status => (int) $endpoint->getAttribute('aggregate'),
                    ])
                    ->all();
            })
            ->all();

        return [
            'outboxes' => $outboxes,
            'deliveries' => $deliveries,
            'latency' => $latency,
            'contracts' => $contracts,
            'endpoints' => $endpoints,
        ];
    }

    public function recordEvent(
        IntegrationDirection $direction,
        string $eventType,
        IntegrationFlowStatus $status
    ): void {
        try {
            $registry = $this->registry();

            if (! $registry) {
                return;
            }
            $counter = $registry->getOrRegisterCounter(
                'app',
                'integration_events_total',
                'Total de eventos de integracao por direcao e status',
                ['direction', 'event_type', 'status']
            );
            $counter->inc([$direction->value, $eventType, $status->value]);
        } catch (\Throwable $exception) {
        }
    }

    public function recordReplay(string $target, IntegrationFlowStatus $status): void
    {
        try {
            $registry = $this->registry();

            if (! $registry) {
                return;
            }
            $counter = $registry->getOrRegisterCounter(
                'app',
                'integration_replays_total',
                'Total de replays operacionais',
                ['target', 'status']
            );
            $counter->inc([$target, $status->value]);
        } catch (\Throwable $exception) {
        }
    }

    public function recordLatency(IntegrationDirection $direction, string $target, int $latencyMs): void
    {
        try {
            $registry = $this->registry();

            if (! $registry) {
                return;
            }
            $histogram = $registry->getOrRegisterHistogram(
                'app',
                'integration_delivery_latency_ms',
                'Latencia das entregas de integracao em milissegundos',
                ['direction', 'target']
            );
            $histogram->observe($latencyMs, [$direction->value, $target]);
        } catch (\Throwable $exception) {
        }
    }

    /**
     * @return list<string>
     */
    private function allDirections(): array
    {
        return array_map(
            fn (IntegrationDirection $direction): string => $direction->value,
            IntegrationDirection::cases()
        );
    }

    /**
     * @return list<string>
     */
    private function allFlowStatuses(): array
    {
        return array_map(
            fn (IntegrationFlowStatus $status): string => $status->value,
            IntegrationFlowStatus::cases()
        );
    }

    /**
     * @return list<string>
     */
    private function knownContractStatuses(): array
    {
        return ['active', 'draft', 'deprecated', 'inactive'];
    }

    public function getMetricFamilySamples(): array
    {
        $registry = $this->registry();

        if (! $registry) {
            return [];
        }

        return $registry->getMetricFamilySamples();
    }

    public function hasBackboneTables(): bool
    {
        return $this->backboneTablesExist();
    }

    private function registry(): ?CollectorRegistry
    {
        try {
            if (extension_loaded('redis') || class_exists(\Redis::class)) {
                return CollectorRegistry::getDefault();
            }

            return self::$fallbackRegistry ??= new CollectorRegistry(new InMemory, false);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function backboneTablesExist(): bool
    {
        try {
            $schema = Schema::connection('tenant');

            return $schema->hasTable('evento_outboxes')
                && $schema->hasTable('entregas_integracao')
                && $schema->hasTable('contratos_evento')
                && $schema->hasTable('endpoints_integracao');
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
