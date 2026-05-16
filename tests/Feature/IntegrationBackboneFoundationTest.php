<?php

namespace Tests\Feature;

use App\Models\EntregaIntegracao;
use App\Models\EventoInbox;
use App\Models\EventoOutbox;
use App\Models\User;
use App\Services\Integration\IntegrationMetrics;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class IntegrationBackboneFoundationTest extends TestCase
{
    public function test_services_config_exposes_backbone_defaults(): void
    {
        $config = config('services.integration_backbone');

        $this->assertSame('integration-outbox', $config['broker']['outbox_queue']);
        $this->assertSame('integration-inbox', $config['broker']['inbox_queue']);
        $this->assertSame('integration-replay', $config['broker']['replay_queue']);
        $this->assertSame(30000, $config['gateway']['timeout_ms']);
        $this->assertSame([30, 120, 600, 1800, 3600], $config['retry']['backoff_seconds']);
    }

    public function test_models_cast_integration_enums_and_payloads(): void
    {
        $outbox = new EventoOutbox([
            'status' => 'pending',
            'payload' => ['vale_id' => 10],
            'metadata' => ['producer' => 'sales'],
            'occurred_at' => now()->toIso8601String(),
        ]);

        $inbox = new EventoInbox([
            'status' => 'processed',
            'duplicate_detected' => true,
            'payload' => ['external_event_id' => 'evt-1'],
            'received_at' => now()->toIso8601String(),
        ]);

        $delivery = new EntregaIntegracao([
            'direction' => 'outbound',
            'transport_kind' => 'broker',
            'status' => 'failed',
            'metadata' => ['target' => 'ms-001'],
        ]);

        $this->assertSame(IntegrationFlowStatus::Pending, $outbox->status);
        $this->assertSame(['vale_id' => 10], $outbox->payload);
        $this->assertSame(IntegrationFlowStatus::Processed, $inbox->status);
        $this->assertTrue($inbox->duplicate_detected);
        $this->assertSame(IntegrationDirection::Outbound, $delivery->direction);
        $this->assertSame(IntegrationTransportKind::Broker, $delivery->transport_kind);
        $this->assertSame(IntegrationFlowStatus::Failed, $delivery->status);
    }

    public function test_only_operational_roles_can_view_and_replay_integration_events(): void
    {
        $gestor = User::factory()->make(['papel' => 'gestor']);
        $vendedor = User::factory()->make(['papel' => 'vendedor']);
        $superAdmin = User::factory()->make(['papel' => 'super_admin']);
        $this->assertTrue(Gate::forUser($gestor)->check('view-integration-operations'));
        $this->assertTrue(Gate::forUser($gestor)->check('replay-integration-events'));
        $this->assertFalse(Gate::forUser($vendedor)->check('view-integration-operations'));
        $this->assertTrue(Gate::forUser($superAdmin)->check('replay-integration-events'));
    }

    public function test_metrics_service_can_be_resolved(): void
    {
        $metrics = app(IntegrationMetrics::class);

        $this->assertInstanceOf(IntegrationMetrics::class, $metrics);
    }
}
