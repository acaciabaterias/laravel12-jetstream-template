<?php

namespace Tests\Unit;

use App\Models\ContratoEvento;
use App\Models\EndpointIntegracao;
use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Services\Integration\EventContractCatalogService;
use App\Services\Integration\IntegrationGatewayRegistry;
use App\Services\Integration\IntegrationMetrics;
use App\Services\Integration\OutboxEventFactory;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegrationContractCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::connection('tenant')->dropIfExists('endpoints_integracao');
        Schema::connection('tenant')->dropIfExists('contratos_evento');
        Schema::connection('tenant')->dropIfExists('entregas_integracao');
        Schema::connection('tenant')->dropIfExists('evento_inboxes');
        Schema::connection('tenant')->dropIfExists('evento_outboxes');

        $this->artisan('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant/2026_05_06_204458_create_integration_backbone_tables.php',
            '--realpath' => false,
        ])->assertExitCode(0);
    }

    public function test_contract_catalog_service_filters_by_event_type(): void
    {
        ContratoEvento::query()->create([
            'event_type' => 'VALE_FATURADO',
            'event_version' => 'v1',
            'producer' => 'sales',
            'status' => 'active',
            'consumers' => ['ms-001'],
        ]);
        ContratoEvento::query()->create([
            'event_type' => 'COBRANCA_CRIAR_BOLETO',
            'event_version' => 'v1',
            'producer' => 'finance',
            'status' => 'active',
            'consumers' => ['ms-002'],
        ]);

        $contracts = app(EventContractCatalogService::class)->list('VALE_FATURADO');

        $this->assertCount(1, $contracts);
        $this->assertSame('VALE_FATURADO', $contracts->first()->event_type);
    }

    public function test_gateway_registry_returns_registered_endpoints(): void
    {
        EndpointIntegracao::query()->create([
            'service_name' => 'ms-fiscal',
            'route_name' => 'emitir-nfe',
            'method' => 'POST',
            'target_url' => 'http://ms-fiscal/api/v1/nfe/emitir',
            'timeout_ms' => 30000,
            'status' => 'active',
        ]);

        $endpoints = app(IntegrationGatewayRegistry::class)->list('ms-fiscal');

        $this->assertCount(1, $endpoints);
        $this->assertSame('emitir-nfe', $endpoints->first()->route_name);
    }

    public function test_metrics_snapshot_aggregates_backbone_operational_state(): void
    {
        $outbox = EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'VALE_FATURADO',
                payload: ['vale_id' => 1],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'metrics-1',
                correlationId: '4afde795-e363-4ae3-ab20-7bd0285f8cd1',
            )
        );
        $outbox->update(['status' => IntegrationFlowStatus::DeadLetter]);

        EntregaIntegracao::query()->create([
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $outbox->id,
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => IntegrationTransportKind::Broker,
            'target' => 'broker:erp-backbone',
            'status' => IntegrationFlowStatus::DeadLetter,
            'attempt_number' => 2,
            'latency_ms' => 180,
        ]);

        ContratoEvento::query()->create([
            'event_type' => 'VALE_FATURADO',
            'event_version' => 'v1',
            'producer' => 'sales',
            'status' => 'active',
            'consumers' => ['ms-001'],
        ]);

        EndpointIntegracao::query()->create([
            'service_name' => 'ms-fiscal',
            'route_name' => 'emitir-nfe',
            'method' => 'POST',
            'target_url' => 'http://ms-fiscal/api/v1/nfe/emitir',
            'timeout_ms' => 30000,
            'status' => 'active',
        ]);

        $snapshot = app(IntegrationMetrics::class)->syncOperationalSnapshot();

        $this->assertSame(1, $snapshot['outboxes']['dead_letter']);
        $this->assertSame(1, $snapshot['deliveries']['outbound']['dead_letter']);
        $this->assertSame(180.0, $snapshot['latency']['outbound']['broker:erp-backbone']);
        $this->assertSame(1, $snapshot['contracts']['active']);
        $this->assertSame(1, $snapshot['endpoints']['ms-fiscal']['active']);

        $metricNames = array_map(
            fn ($sample): string => $sample->getName(),
            app(IntegrationMetrics::class)->getMetricFamilySamples()
        );

        $this->assertContains('app_integration_outbox_total', $metricNames);
        $this->assertContains('app_integration_deliveries_total', $metricNames);
        $this->assertContains('app_integration_delivery_latency_average_ms', $metricNames);
    }
}
