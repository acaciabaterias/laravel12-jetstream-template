<?php

namespace Tests\Feature;

use App\Models\EventoInbox;
use App\Services\Contracts\Integration\InboundEventConsumerContract;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegrationBackboneInboxTest extends TestCase
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

    public function test_it_consumes_new_event_into_inbox(): void
    {
        $consumer = app(InboundEventConsumerContract::class);

        $inbox = $consumer->consume(
            eventType: 'COBRANCA_PAGA',
            producer: 'ms-002',
            payload: ['boleto_id' => 15],
            tenantExternalRef: 'tenant-a',
            externalEventId: 'evt-001',
            idempotencyKey: 'idemp-001',
            correlationId: '64547c8c-4400-4c0f-a8a5-6649ab387437',
        );

        $this->assertDatabaseHas('evento_inboxes', [
            'id' => $inbox->id,
            'external_event_id' => 'evt-001',
            'producer' => 'ms-002',
            'duplicate_detected' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('entregas_integracao', [
            'entregavel_type' => EventoInbox::class,
            'entregavel_id' => $inbox->id,
            'target' => 'ms-002',
            'status' => 'processed',
        ], 'tenant');
    }

    public function test_it_marks_duplicate_event_without_reprocessing(): void
    {
        $consumer = app(InboundEventConsumerContract::class);

        $first = $consumer->consume(
            eventType: 'COBRANCA_PAGA',
            producer: 'ms-002',
            payload: ['boleto_id' => 15],
            tenantExternalRef: 'tenant-a',
            externalEventId: 'evt-dup',
            idempotencyKey: 'idemp-dup',
            correlationId: '3c212e9b-9f3f-4541-a569-a7523bc76217',
        );

        $second = $consumer->consume(
            eventType: 'COBRANCA_PAGA',
            producer: 'ms-002',
            payload: ['boleto_id' => 15],
            tenantExternalRef: 'tenant-a',
            externalEventId: 'evt-dup',
            idempotencyKey: 'idemp-dup',
            correlationId: '3c212e9b-9f3f-4541-a569-a7523bc76217',
        );

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseHas('evento_inboxes', [
            'id' => $first->id,
            'duplicate_detected' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('entregas_integracao', [
            'entregavel_type' => EventoInbox::class,
            'entregavel_id' => $first->id,
            'status' => 'skipped',
        ], 'tenant');
    }
}
