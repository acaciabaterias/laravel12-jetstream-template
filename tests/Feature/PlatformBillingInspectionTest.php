<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use App\Services\Billing\SaasInvoiceService;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformBillingInspectionTest extends TestCase
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

    public function test_billing_inspection_endpoint_returns_filtered_central_subscriptions(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $plano = PlanoComercial::factory()->create(['slug' => 'pro']);
        $politica = PoliticaInadimplencia::factory()->create([
            'grace_period_days' => 2,
            'block_after_days' => 5,
        ]);
        $cliente = Cliente::factory()->create([
            'razao_social' => 'Cliente Inspecionado',
            'status' => 'active',
            'subdominio' => 'cliente-inspecionado',
        ]);

        $assinatura = app(SubscriptionLifecycleService::class)->activate(
            cliente: $cliente,
            planoComercial: $plano,
            politicaInadimplencia: $politica,
        );

        app(SaasInvoiceService::class)->createInvoice($assinatura, [
            'vencimento' => Carbon::now()->subDays(8)->toDateString(),
            'valor' => 250.00,
        ]);
        app(DelinquencyPolicyEvaluator::class)->assess($assinatura, Carbon::now());

        $response = $this->actingAs($operador, 'platform')
            ->getJson(route('admin.billing.inspection', ['risk' => 'blocked']));

        $response->assertOk()
            ->assertJsonPath('summary.blocked_subscriptions', 1)
            ->assertJsonPath('subscriptions.0.tenant_name', 'Cliente Inspecionado')
            ->assertJsonPath('subscriptions.0.status', 'blocked');
    }
}
