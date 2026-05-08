<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\EventoComercialAssinante;
use App\Models\EventoOutbox;
use App\Models\PlanoComercial;
use App\Services\Billing\PlatformBillingEventPublisher;
use App\Services\Billing\SubscriptionLifecycleService;
use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlatformBillingAuditAndPublicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_billing.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_platform_billing_event_publisher_creates_central_contract_and_outbox_payload(): void
    {
        Queue::fake();

        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-audit',
        ]);
        $plano = PlanoComercial::factory()->create([
            'slug' => 'scale',
        ]);
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'status' => 'active',
        ])->loadMissing('cliente', 'plano');

        app(PlatformBillingEventPublisher::class)->publish(
            eventType: 'ASSINATURA_ATIVADA',
            assinaturaPlataforma: $assinatura,
            payload: [
                'subscription_id' => $assinatura->id,
                'tenant_id' => $cliente->id,
                'plan_slug' => $plano->slug,
            ],
            consumers: ['platform', 'ms-003'],
            schemaDefinition: ['subscription_id' => 'integer', 'tenant_id' => 'integer', 'plan_slug' => 'string'],
        );

        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'ASSINATURA_ATIVADA',
            'event_version' => 'v1',
            'producer' => 'platform-billing',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ASSINATURA_ATIVADA',
            'tenant_external_ref' => 'tenant-audit',
            'origin_context' => 'platform-billing',
        ], 'central');

        $outbox = app(IntegrationStorageManager::class)->using('central', static fn (): EventoOutbox => EventoOutbox::query()->firstOrFail());

        $this->assertSame([
            'subscription_id' => $assinatura->id,
            'tenant_id' => $cliente->id,
            'plan_slug' => 'scale',
        ], $outbox->payload);
        $this->assertSame(['platform', 'ms-003'], $outbox->metadata['consumers']);
        $this->assertSame('broker:platform-billing', $outbox->metadata['target']);
        $this->assertIsInt($outbox->metadata['contract_id']);
    }

    public function test_commercial_event_model_casts_state_payloads_and_effective_at(): void
    {
        $evento = EventoComercialAssinante::factory()->create([
            'before_state' => ['status' => 'trial'],
            'after_state' => ['status' => 'active', 'data_inicio' => '2026-05-08'],
            'metadata' => ['plano_slug' => 'pro', 'source' => 'unit-test'],
            'effective_at' => '2026-05-08 10:30:00',
        ])->fresh();

        $this->assertSame(['status' => 'trial'], $evento->before_state);
        $this->assertSame(['status' => 'active', 'data_inicio' => '2026-05-08'], $evento->after_state);
        $this->assertSame(['plano_slug' => 'pro', 'source' => 'unit-test'], $evento->metadata);
        $this->assertSame('2026-05-08 10:30:00', $evento->effective_at?->format('Y-m-d H:i:s'));
    }

    public function test_subscription_lifecycle_records_a_serialized_audit_snapshot(): void
    {
        $cliente = Cliente::factory()->create([
            'status' => 'trial',
            'subdominio' => 'tenant-snapshot',
        ]);
        $plano = PlanoComercial::factory()->create([
            'slug' => 'growth',
        ]);

        $assinatura = app(SubscriptionLifecycleService::class)->activate(
            cliente: $cliente,
            planoComercial: $plano,
            attributes: [
                'reason' => 'Ativacao auditada.',
            ],
        );

        /** @var EventoComercialAssinante $evento */
        $evento = EventoComercialAssinante::query()
            ->where('assinatura_id', $assinatura->id)
            ->firstOrFail();

        $this->assertNull($evento->before_state);
        $this->assertSame('subscription_activated', $evento->event_type);
        $this->assertSame('Ativacao auditada.', $evento->reason);
        $this->assertSame('growth', $evento->metadata['plano_slug']);
        $this->assertSame([
            'status' => 'active',
            'plano_id' => $plano->id,
            'data_inicio' => $assinatura->data_inicio?->toDateString(),
            'data_proximo_ciclo' => $assinatura->data_proximo_ciclo?->toDateString(),
            'data_termino' => null,
            'cancel_reason' => null,
        ], $evento->after_state);
    }
}
