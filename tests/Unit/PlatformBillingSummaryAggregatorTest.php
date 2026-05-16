<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use App\Services\Billing\PlatformBillingSummaryService;
use App\Services\Billing\SaasInvoiceService;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformBillingSummaryAggregatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_it_aggregates_mrr_and_operational_subscription_statuses(): void
    {
        $planoAtivo = PlanoComercial::factory()->create([
            'nome' => 'Pro',
            'preco_mensal' => 199.90,
        ]);
        $planoBloqueado = PlanoComercial::factory()->create([
            'nome' => 'Enterprise',
            'preco_mensal' => 599.90,
        ]);
        $politica = PoliticaInadimplencia::factory()->create([
            'grace_period_days' => 2,
            'block_after_days' => 5,
        ]);

        $assinaturaAtiva = app(SubscriptionLifecycleService::class)->activate(
            cliente: Cliente::factory()->create(['status' => 'active']),
            planoComercial: $planoAtivo,
            politicaInadimplencia: $politica,
        );
        $assinaturaBloqueada = app(SubscriptionLifecycleService::class)->activate(
            cliente: Cliente::factory()->create(['status' => 'active']),
            planoComercial: $planoBloqueado,
            politicaInadimplencia: $politica,
        );

        app(SaasInvoiceService::class)->createInvoice($assinaturaBloqueada, [
            'vencimento' => Carbon::now()->subDays(9)->toDateString(),
            'valor' => 599.90,
        ]);
        app(DelinquencyPolicyEvaluator::class)->assess($assinaturaBloqueada, Carbon::now());

        $summary = app(PlatformBillingSummaryService::class)->summarize();

        $this->assertSame(1, $summary['active_subscriptions']);
        $this->assertSame(1, $summary['blocked_subscriptions']);
        $this->assertSame(799.8, $summary['mrr']);
        $this->assertSame(599.9, $summary['overdue_exposure']);
    }
}
