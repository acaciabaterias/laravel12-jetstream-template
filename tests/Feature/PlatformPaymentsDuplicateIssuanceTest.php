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
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformPaymentsDuplicateIssuanceTest extends TestCase
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

    public function test_duplicate_issuance_returns_existing_charge_without_creating_a_new_one(): void
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
        $gateway = GatewayCobrancaSaaS::factory()->create();
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $service = app(ExternalChargeIssuanceService::class);

        $primeira = $service->issue($fatura, $gateway, 'pix', $operador, false, 'Primeira emissao.');
        $segunda = $service->issue($fatura->fresh(), $gateway, 'pix', $operador, false, 'Reenvio indevido.');

        $this->assertSame($primeira->id, $segunda->id);
        $this->assertDatabaseCount('cobrancas_saas_externas', 1, 'central');
    }

    public function test_controlled_reissue_creates_a_new_charge_and_links_previous_attempt(): void
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
        $gateway = GatewayCobrancaSaaS::factory()->create();
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $service = app(ExternalChargeIssuanceService::class);

        $primeira = $service->issue($fatura, $gateway, 'boleto', $operador, false, 'Primeira emissao.');
        $segunda = $service->issue($fatura->fresh(), $gateway, 'boleto', $operador, true, 'Reemissao autorizada.');

        $this->assertNotSame($primeira->id, $segunda->id);
        $this->assertSame($primeira->id, $segunda->metadata['reissued_from_charge_id']);
        $this->assertDatabaseCount('cobrancas_saas_externas', 2, 'central');
    }
}
