<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use App\Services\Billing\SaasInvoiceService;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformBillingBackbonePublicationTest extends TestCase
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

    public function test_subscription_lifecycle_publishes_central_backbone_events(): void
    {
        $cliente = Cliente::factory()->create([
            'status' => 'trial',
            'subdominio' => 'tenant-core',
        ]);
        $planoInicial = PlanoComercial::factory()->create(['slug' => 'essential']);
        $planoNovo = PlanoComercial::factory()->create(['slug' => 'enterprise']);

        $service = app(SubscriptionLifecycleService::class);

        $assinatura = $service->activate(
            cliente: $cliente,
            planoComercial: $planoInicial,
        );

        $service->changePlan(
            assinatura: $assinatura,
            novoPlano: $planoNovo,
            attributes: ['reason' => 'Upgrade comercial.'],
        );

        $service->cancel(
            assinatura: $assinatura->refresh(),
            reason: 'Encerramento contratual.',
        );

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ASSINATURA_ATIVADA',
            'tenant_external_ref' => 'tenant-core',
            'origin_context' => 'platform-billing',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'PLANO_ALTERADO',
            'tenant_external_ref' => 'tenant-core',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ASSINATURA_CANCELADA',
            'tenant_external_ref' => 'tenant-core',
        ], 'central');

        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'ASSINATURA_ATIVADA',
            'producer' => 'platform-billing',
        ], 'central');
    }

    public function test_delinquency_flow_publishes_block_and_reactivation_events_to_central_backbone(): void
    {
        $cliente = Cliente::factory()->create([
            'status' => 'active',
            'subdominio' => 'tenant-risk',
        ]);
        $plano = PlanoComercial::factory()->create(['slug' => 'pro']);
        $politica = PoliticaInadimplencia::factory()->create([
            'grace_period_days' => 2,
            'block_after_days' => 5,
        ]);

        $assinatura = app(SubscriptionLifecycleService::class)->activate(
            cliente: $cliente,
            planoComercial: $plano,
            politicaInadimplencia: $politica,
        );

        $fatura = app(SaasInvoiceService::class)->createInvoice($assinatura, [
            'vencimento' => Carbon::now()->subDays(8)->toDateString(),
            'valor' => 350.00,
        ]);

        app(DelinquencyPolicyEvaluator::class)->assess($assinatura, Carbon::now());
        app(SaasInvoiceService::class)->markAsPaid($fatura);
        app(DelinquencyPolicyEvaluator::class)->assess($assinatura->refresh(), Carbon::now());

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ASSINANTE_BLOQUEADO',
            'tenant_external_ref' => 'tenant-risk',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ASSINANTE_REATIVADO',
            'tenant_external_ref' => 'tenant-risk',
        ], 'central');

        $this->assertDatabaseHas('entregas_integracao', [
            'target' => 'broker:platform-billing',
            'direction' => 'outbound',
        ], 'central');
    }
}
