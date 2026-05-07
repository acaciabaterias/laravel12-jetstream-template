<?php

namespace Tests\Feature;

use App\Models\ContratoEvento;
use App\Models\EndpointIntegracao;
use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Models\Filial;
use App\Models\User;
use App\Services\Integration\OutboxEventFactory;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegrationGatewayInspectionTest extends TestCase
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

    public function test_inspection_endpoint_filters_deliveries_and_returns_catalog_data(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->create([
            'papel' => 'gestor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);

        $outbox = EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'VALE_FATURADO',
                payload: ['vale_id' => 99],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'inspect-99',
                correlationId: 'f2f08472-8457-4a8c-887d-17d3cf9cb30f',
            )
        );

        EntregaIntegracao::query()->create([
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $outbox->id,
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => IntegrationTransportKind::Broker,
            'target' => 'broker:erp-backbone',
            'status' => IntegrationFlowStatus::Failed,
            'attempt_number' => 2,
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

        $response = $this->actingAs($user)->getJson('/api/integration/inspections?status=failed&event_type=VALE_FATURADO');

        $response->assertOk()
            ->assertJsonPath('summary.deliveries', 1)
            ->assertJsonPath('summary.contracts', 1)
            ->assertJsonPath('summary.endpoints', 1)
            ->assertJsonPath('metrics.deliveries.outbound.failed', 1)
            ->assertJsonPath('deliveries.0.status', 'failed')
            ->assertJsonPath('contracts.0.event_type', 'VALE_FATURADO');
    }

    public function test_replay_endpoint_requeues_dead_letter_delivery(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->create([
            'papel' => 'gestor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);

        $outbox = EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'COBRANCA_CRIAR_BOLETO',
                payload: ['vale_id' => 100],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'inspect-100',
                correlationId: '0925d9dc-1853-4cb0-a244-f67fef40af0b',
            )
        );
        $outbox->update(['status' => IntegrationFlowStatus::DeadLetter]);

        $delivery = EntregaIntegracao::query()->create([
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $outbox->id,
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => IntegrationTransportKind::Broker,
            'target' => 'broker:erp-backbone',
            'status' => IntegrationFlowStatus::DeadLetter,
            'attempt_number' => 3,
        ]);

        $response = $this->actingAs($user)->postJson('/api/integration/inspections/replay', [
            'delivery_id' => $delivery->id,
            'reason' => 'api replay',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'replayed');

        $outbox->refresh();
        $this->assertSame(IntegrationFlowStatus::Pending, $outbox->status);
    }
}
