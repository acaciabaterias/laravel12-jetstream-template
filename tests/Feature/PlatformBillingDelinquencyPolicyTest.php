<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use App\Services\Billing\SaasInvoiceService;
use App\Services\Billing\SubscriptionLifecycleService;
use App\Support\Billing\CommercialEventType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformBillingDelinquencyPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runCentralBillingMigrations();
    }

    public function test_it_marks_an_invoice_as_overdue_and_moves_the_subscription_into_grace_period(): void
    {
        $cliente = Cliente::factory()->create(['status' => 'active']);
        $plano = PlanoComercial::factory()->create(['slug' => 'pro']);
        $politica = PoliticaInadimplencia::factory()->create([
            'grace_period_days' => 3,
            'block_after_days' => 7,
        ]);

        $assinatura = app(SubscriptionLifecycleService::class)->activate(
            cliente: $cliente,
            planoComercial: $plano,
            politicaInadimplencia: $politica,
        );

        $fatura = app(SaasInvoiceService::class)->createInvoice($assinatura, [
            'referencia' => '2026-05',
            'vencimento' => Carbon::now()->subDays(4)->toDateString(),
            'valor' => 199.90,
        ]);

        $avaliada = app(DelinquencyPolicyEvaluator::class)->assess(
            assinaturaPlataforma: $assinatura,
            referenceDate: Carbon::now(),
        );

        $this->assertSame('grace_period', $avaliada->status);
        $this->assertNotNull($avaliada->grace_ends_at);
        $this->assertSame('overdue', $fatura->refresh()->status);

        $cliente->refresh();
        $this->assertFalse($cliente->billing_blocked);

        $this->assertDatabaseHas('eventos_comerciais_assinante', [
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'event_type' => CommercialEventType::GraceStarted->value,
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
