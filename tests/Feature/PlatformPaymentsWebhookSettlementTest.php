<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Models\PlanoComercial;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\ExternalChargeIssuanceService;
use App\Services\Billing\PaymentWebhookIngestionService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformPaymentsWebhookSettlementTest extends TestCase
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

    public function test_payment_webhook_marks_invoice_paid_and_publishes_settlement_event(): void
    {
        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-liquidado',
            'status' => 'active',
        ]);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
            'status' => 'active',
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'valor' => 320.50,
            'status' => 'pending',
            'referencia' => '2026-06',
        ]);
        $gateway = GatewayCobrancaSaaS::factory()->create();
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $cobranca = app(ExternalChargeIssuanceService::class)->issue($fatura, $gateway, 'pix', $operador);

        $retorno = app(PaymentWebhookIngestionService::class)->ingest($gateway, [
            'external_event_id' => 'evt-1',
            'external_reference' => $cobranca->external_reference,
            'external_charge_id' => $cobranca->external_charge_id,
            'event_type' => 'payment_received',
            'amount' => 320.50,
        ], actor: $operador);

        $this->assertSame('processed', $retorno->processing_status->value);
        $this->assertDatabaseHas('faturas', [
            'id' => $fatura->id,
            'status' => 'paid',
            'valor_pago' => 320.50,
        ], 'central');
        $this->assertDatabaseHas('conciliacoes_pagamento_saas', [
            'fatura_saas_id' => $fatura->id,
            'status' => 'matched',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'COBRANCA_SAAS_LIQUIDADA',
            'tenant_external_ref' => 'tenant-liquidado',
        ], 'central');
    }
}
