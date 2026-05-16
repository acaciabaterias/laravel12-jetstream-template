<?php

namespace Tests\Feature;

use App\Jobs\DispatchOutboxEventJob;
use App\Models\ContratoEvento;
use App\Models\EventoOutbox;
use App\Services\Integration\EventPublisher;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IntegrationBackbonePublicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant/2026_05_06_204458_create_integration_backbone_tables.php',
            '--realpath' => false,
        ])->assertExitCode(0);
    }

    public function test_it_persists_outbox_event_and_dispatches_async_job(): void
    {
        Queue::fake();

        $publisher = app(EventPublisher::class);

        $event = $publisher->publish(
            eventType: 'VALE_FATURADO',
            payload: ['vale_id' => 10, 'pedido_venda_id' => 22],
            tenantExternalRef: 'tenant-a',
            idempotencyKey: 'vale-faturado-10',
            correlationId: 'f4f65d20-2a41-4c5d-9e77-08adf46b3e11',
            originContext: 'sales',
            metadata: [
                'consumers' => ['ms-001', 'ms-003'],
                'target' => 'broker:erp-backbone',
            ],
        );

        $this->assertInstanceOf(EventoOutbox::class, $event);
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'VALE_FATURADO',
            'tenant_external_ref' => 'tenant-a',
            'idempotency_key' => 'vale-faturado-10',
        ], 'tenant');
        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'VALE_FATURADO',
            'event_version' => 'v1',
            'producer' => 'sales',
        ], 'tenant');

        Queue::assertPushed(DispatchOutboxEventJob::class, function (DispatchOutboxEventJob $job) use ($event): bool {
            return $job->eventoOutboxId === $event->id;
        });
    }

    public function test_it_registers_contract_once_for_same_event_version(): void
    {
        $publisher = app(EventPublisher::class);

        $publisher->publish(
            eventType: 'COBRANCA_CRIAR_BOLETO',
            payload: ['vale_id' => 1],
            tenantExternalRef: 'tenant-a',
            idempotencyKey: 'boleto-1',
            correlationId: 'b30ea2d6-9b07-43ec-9883-b97a253f1550',
            originContext: 'finance',
            metadata: ['consumers' => ['ms-002']]
        );

        $publisher->publish(
            eventType: 'COBRANCA_CRIAR_BOLETO',
            payload: ['vale_id' => 2],
            tenantExternalRef: 'tenant-b',
            idempotencyKey: 'boleto-2',
            correlationId: '2cceece6-7ad1-4772-b6d0-6ab341f9d125',
            originContext: 'finance',
            metadata: ['consumers' => ['ms-002']]
        );

        $this->assertSame(1, ContratoEvento::query()
            ->where('event_type', 'COBRANCA_CRIAR_BOLETO')
            ->where('event_version', 'v1')
            ->count());
    }
}
