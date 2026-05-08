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

class PlatformPaymentsExceptionFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_payments.events.publish_to_backbone', false);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_payment_inspection_endpoint_filters_by_exception_type(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $cliente = Cliente::factory()->create([
            'razao_social' => 'Cliente Divergente',
        ]);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'valor' => 180.00,
        ]);
        $gateway = GatewayCobrancaSaaS::factory()->create();
        $cobranca = app(ExternalChargeIssuanceService::class)->issue($fatura, $gateway, 'boleto', $operador);

        app(PaymentWebhookIngestionService::class)->ingest($gateway, [
            'external_event_id' => 'evt-exception',
            'external_reference' => $cobranca->external_reference,
            'external_charge_id' => $cobranca->external_charge_id,
            'event_type' => 'payment_received',
            'amount' => 100.00,
        ], actor: $operador);

        $response = $this->actingAs($operador, 'platform')
            ->getJson(route('admin.payments.inspection', ['exception' => 'amount_mismatch']));

        $response->assertOk()
            ->assertJsonPath('summary.open_exceptions', 1)
            ->assertJsonPath('charges.0.tenant_name', 'Cliente Divergente')
            ->assertJsonPath('charges.0.exceptions.0.type', 'amount_mismatch');
    }
}
