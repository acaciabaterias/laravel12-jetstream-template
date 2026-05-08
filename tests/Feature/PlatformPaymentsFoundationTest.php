<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\CobrancaSaaSExterna;
use App\Models\ConciliacaoPagamentoSaaS;
use App\Models\ExcecaoConciliacaoSaaS;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Models\PlanoComercial;
use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\PlatformPaymentsEventPublisher;
use App\Support\Billing\ExternalChargeStatus;
use App\Support\Billing\GatewayOperationalStatus;
use App\Support\Billing\PaymentExceptionStatus;
use App\Support\Billing\PaymentReconciliationStatus;
use App\Support\Billing\PaymentReturnProcessingStatus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformPaymentsFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_payments.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_central_payment_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('gateways_cobranca_saas'));
        $this->assertTrue(Schema::connection('central')->hasTable('cobrancas_saas_externas'));
        $this->assertTrue(Schema::connection('central')->hasTable('retornos_pagamento_saas'));
        $this->assertTrue(Schema::connection('central')->hasTable('conciliacoes_pagamento_saas'));
        $this->assertTrue(Schema::connection('central')->hasTable('excecoes_conciliacao_saas'));
    }

    public function test_payment_models_persist_relationships_and_enum_casts(): void
    {
        $cliente = Cliente::factory()->create();
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
        ]);
        $gateway = GatewayCobrancaSaaS::factory()->create([
            'status' => GatewayOperationalStatus::Active->value,
        ]);
        $cobranca = CobrancaSaaSExterna::factory()->create([
            'fatura_saas_id' => $fatura->id,
            'gateway_cobranca_saas_id' => $gateway->id,
            'status' => ExternalChargeStatus::Pending->value,
        ]);
        $retorno = RetornoPagamentoSaaS::factory()->create([
            'gateway_cobranca_saas_id' => $gateway->id,
            'cobranca_saas_externa_id' => $cobranca->id,
            'processing_status' => PaymentReturnProcessingStatus::Pending->value,
        ]);
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $conciliacao = ConciliacaoPagamentoSaaS::factory()->create([
            'fatura_saas_id' => $fatura->id,
            'cobranca_saas_externa_id' => $cobranca->id,
            'retorno_pagamento_saas_id' => $retorno->id,
            'operator_user_id' => $operador->id,
            'status' => PaymentReconciliationStatus::Matched->value,
        ]);
        $excecao = ExcecaoConciliacaoSaaS::factory()->create([
            'fatura_saas_id' => $fatura->id,
            'cobranca_saas_externa_id' => $cobranca->id,
            'retorno_pagamento_saas_id' => $retorno->id,
            'conciliacao_pagamento_saas_id' => $conciliacao->id,
            'owner_user_id' => $operador->id,
            'status' => PaymentExceptionStatus::Open->value,
        ]);

        $this->assertSame('central', $gateway->getConnectionName());
        $this->assertSame($gateway->id, $cobranca->gateway->id);
        $this->assertSame($fatura->id, $cobranca->fatura->id);
        $this->assertSame($retorno->id, $conciliacao->retorno->id);
        $this->assertSame($excecao->id, $fatura->excecoesConciliacao()->firstOrFail()->id);
        $this->assertSame(GatewayOperationalStatus::Active, $gateway->status);
        $this->assertSame(ExternalChargeStatus::Pending, $cobranca->status);
        $this->assertSame(PaymentReturnProcessingStatus::Pending, $retorno->processing_status);
        $this->assertSame(PaymentReconciliationStatus::Matched, $conciliacao->status);
        $this->assertSame(PaymentExceptionStatus::Open, $excecao->status);
    }

    public function test_platform_payment_permissions_are_restricted_to_platform_billing_roles(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $support = UsuarioPlataforma::factory()->create();

        $this->assertTrue(Gate::forUser($billing)->allows('manage-platform-payments'));
        $this->assertFalse(Gate::forUser($support)->allows('manage-platform-payments'));
    }

    public function test_payment_event_publisher_creates_contract_and_central_outbox_record(): void
    {
        Queue::fake();

        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-payments',
        ]);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'referencia' => '2026-05',
        ])->loadMissing('cliente');

        app(PlatformPaymentsEventPublisher::class)->publish(
            eventType: 'COBRANCA_SAAS_EMITIDA',
            faturaSaaS: $fatura,
            payload: [
                'invoice_id' => $fatura->id,
                'tenant_id' => $cliente->id,
                'reference' => $fatura->referencia,
            ],
            consumers: ['platform', 'analytics'],
            schemaDefinition: ['invoice_id' => 'integer', 'tenant_id' => 'integer', 'reference' => 'string'],
        );

        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'COBRANCA_SAAS_EMITIDA',
            'producer' => 'platform-payments',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'COBRANCA_SAAS_EMITIDA',
            'tenant_external_ref' => 'tenant-payments',
            'origin_context' => 'platform-payments',
        ], 'central');
    }
}
