<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use App\Services\Billing\SaasInvoiceService;
use App\Services\Billing\SubscriptionLifecycleService;
use App\Services\BillingAccessGuard;
use App\Support\Billing\CommercialEventType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformBillingBlockReactivationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runCentralBillingMigrations();
    }

    public function test_it_blocks_and_reactivates_the_subscriber_after_payment_regularization(): void
    {
        $cliente = Cliente::factory()->create(['status' => 'active']);
        $plano = PlanoComercial::factory()->create(['slug' => 'enterprise']);
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
            'referencia' => '2026-05',
            'vencimento' => Carbon::now()->subDays(8)->toDateString(),
            'valor' => 499.90,
        ]);

        $bloqueada = app(DelinquencyPolicyEvaluator::class)->assess(
            assinaturaPlataforma: $assinatura,
            referenceDate: Carbon::now(),
        );

        $this->assertSame('blocked', $bloqueada->status);
        $this->assertTrue(app(BillingAccessGuard::class)->shouldBlockClienteAccess($cliente->id));

        $cliente->refresh();
        $this->assertSame('suspended', $cliente->status);
        $this->assertTrue($cliente->billing_blocked);

        app(SaasInvoiceService::class)->markAsPaid($fatura);

        $reativada = app(DelinquencyPolicyEvaluator::class)->assess(
            assinaturaPlataforma: $assinatura->refresh(),
            referenceDate: Carbon::now(),
        );

        $this->assertSame('active', $reativada->status);

        $cliente->refresh();
        $this->assertSame('active', $cliente->status);
        $this->assertFalse($cliente->billing_blocked);
        $this->assertFalse(app(BillingAccessGuard::class)->shouldBlockClienteAccess($cliente->id));

        $this->assertDatabaseHas('eventos_comerciais_assinante', [
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'event_type' => CommercialEventType::SubscriberBlocked->value,
        ], 'central');

        $this->assertDatabaseHas('eventos_comerciais_assinante', [
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'event_type' => CommercialEventType::SubscriberReactivated->value,
        ], 'central');
    }

    private function runCentralBillingMigrations(): void
    {
        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }
}
