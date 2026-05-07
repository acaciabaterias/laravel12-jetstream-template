<?php

namespace Tests\Feature;

use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Models\User;
use App\Services\Contracts\Integration\IntegrationReplayServiceContract;
use App\Services\Integration\OutboxEventFactory;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegrationBackboneReplayFlowTest extends TestCase
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

    public function test_it_replays_dead_letter_delivery_and_requeues_deliverable(): void
    {
        $operator = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);

        $outbox = EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'VALE_FATURADO',
                payload: ['vale_id' => 44],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'dead-letter-44',
                correlationId: '6b6d21d8-c0be-4aca-b51f-2834ae591607',
            )
        );

        $outbox->update([
            'status' => IntegrationFlowStatus::DeadLetter,
            'last_error' => 'broker offline',
        ]);

        $delivery = EntregaIntegracao::query()->create([
            'entregavel_type' => EventoOutbox::class,
            'entregavel_id' => $outbox->id,
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => IntegrationTransportKind::Broker,
            'target' => 'broker:erp-backbone',
            'status' => IntegrationFlowStatus::DeadLetter,
            'attempt_number' => 2,
        ]);

        $replay = app(IntegrationReplayServiceContract::class)->replay($delivery, $operator, [
            'reason' => 'manual replay',
        ]);

        $outbox->refresh();

        $this->assertSame(IntegrationFlowStatus::Pending, $outbox->status);
        $this->assertNull($outbox->last_error);
        $this->assertDatabaseHas('entregas_integracao', [
            'id' => $replay->id,
            'replayed_from_entrega_id' => $delivery->id,
            'status' => 'replayed',
        ], 'tenant');
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $operator->id,
            'action' => 'replayed',
            'table_name' => 'entregas_integracao',
            'record_id' => $delivery->id,
        ]);
    }

    public function test_replay_command_requeues_delivery(): void
    {
        $operator = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);

        $outbox = EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'COBRANCA_CRIAR_BOLETO',
                payload: ['vale_id' => 45],
                tenantExternalRef: 'tenant-b',
                idempotencyKey: 'dead-letter-45',
                correlationId: 'c2200aeb-a5f2-4ee8-93b7-ccf4db55f673',
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

        $this->artisan('integration:replay', [
            'delivery_id' => $delivery->id,
            '--operator' => $operator->id,
        ])->assertExitCode(0);

        $outbox->refresh();
        $this->assertSame(IntegrationFlowStatus::Pending, $outbox->status);
    }
}
