<?php

namespace Tests\Feature;

use App\Jobs\DispatchOutboxEventJob;
use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Services\Integration\IntegrationMetrics;
use App\Services\Integration\OutboundDeliveryTracker;
use App\Services\Integration\OutboxEventFactory;
use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegrationBackboneRetryTest extends TestCase
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

    public function test_it_retries_failed_dispatch_and_keeps_event_pending_before_dead_letter(): void
    {
        config()->set('services.integration_backbone.retry.max_attempts', 3);
        config()->set('services.integration_backbone.retry.backoff_seconds', [30, 60, 120]);

        $event = EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'VALE_FATURADO',
                payload: ['vale_id' => 10],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'retry-vale-10',
                correlationId: 'b03b65b3-b048-4f0a-9d6d-fd237f5fcbc9',
                metadata: [
                    'simulate_failure' => true,
                    'failure_message' => 'broker offline',
                    'target' => 'broker:erp-backbone',
                ],
            )
        );

        app(DispatchOutboxEventJob::class, ['eventoOutboxId' => $event->id])
            ->handle(app(IntegrationMetrics::class), app(OutboundDeliveryTracker::class));

        $event->refresh();

        $this->assertSame(IntegrationFlowStatus::Pending, $event->status);
        $this->assertSame(1, $event->attempts);
        $this->assertSame('broker offline', $event->last_error);
        $this->assertDatabaseHas('entregas_integracao', [
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $event->id,
            'attempt_number' => 1,
            'status' => IntegrationFlowStatus::Failed->value,
        ], 'tenant');
    }

    public function test_it_moves_event_to_dead_letter_after_max_attempts(): void
    {
        config()->set('services.integration_backbone.retry.max_attempts', 2);
        config()->set('services.integration_backbone.retry.backoff_seconds', [30, 60]);

        $event = EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'COBRANCA_CRIAR_BOLETO',
                payload: ['vale_id' => 11],
                tenantExternalRef: 'tenant-b',
                idempotencyKey: 'retry-vale-11',
                correlationId: 'cecbecfe-2d2c-4879-877a-ae98b8b9046a',
                metadata: [
                    'simulate_failure' => true,
                    'failure_message' => 'permanent outage',
                    'target' => 'broker:erp-backbone',
                ],
            )
        );

        $job = app(DispatchOutboxEventJob::class, ['eventoOutboxId' => $event->id]);
        $job->handle(app(IntegrationMetrics::class), app(OutboundDeliveryTracker::class));
        $job->handle(app(IntegrationMetrics::class), app(OutboundDeliveryTracker::class));

        $event->refresh();

        $this->assertSame(IntegrationFlowStatus::DeadLetter, $event->status);
        $this->assertSame(2, $event->attempts);
        $this->assertDatabaseHas('entregas_integracao', [
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $event->id,
            'attempt_number' => 2,
            'status' => IntegrationFlowStatus::DeadLetter->value,
        ], 'tenant');
        $this->assertSame(2, EntregaIntegracao::query()->where('entregavel_id', $event->id)->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'dead_letter',
            'table_name' => 'entregas_integracao',
        ]);
    }
}
