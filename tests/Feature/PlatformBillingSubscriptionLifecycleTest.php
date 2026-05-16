<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformBillingSubscriptionLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runCentralBillingMigrations();
    }

    public function test_it_activates_a_subscription_and_records_the_initial_event(): void
    {
        $cliente = Cliente::factory()->create([
            'status' => 'trial',
            'plano_atual_id' => null,
        ]);
        $plano = PlanoComercial::factory()->create([
            'slug' => 'pro',
        ]);
        $politica = PoliticaInadimplencia::factory()->create([
            'grace_period_days' => 5,
        ]);
        $actor = UsuarioPlataforma::factory()->billing()->create();

        $assinatura = app(SubscriptionLifecycleService::class)->activate(
            cliente: $cliente,
            planoComercial: $plano,
            politicaInadimplencia: $politica,
            attributes: [
                'reason' => 'Onboarding concluido.',
            ],
            actor: $actor,
        );

        $this->assertSame('active', $assinatura->status);
        $this->assertSame($plano->id, $assinatura->plano_id);
        $this->assertSame($politica->id, $assinatura->politica_inadimplencia_id);

        $cliente->refresh();
        $this->assertSame('active', $cliente->status);
        $this->assertSame($plano->id, $cliente->plano_atual_id);
        $this->assertFalse($cliente->billing_blocked);

        $this->assertDatabaseHas('eventos_comerciais_assinante', [
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'actor_user_id' => $actor->id,
            'event_type' => 'subscription_activated',
        ], 'central');
    }

    public function test_it_cancels_an_active_subscription_and_updates_the_customer_status(): void
    {
        $cliente = Cliente::factory()->create([
            'status' => 'active',
        ]);
        $plano = PlanoComercial::factory()->create();
        $actor = UsuarioPlataforma::factory()->superAdmin()->create();

        $assinatura = app(SubscriptionLifecycleService::class)->activate(
            cliente: $cliente,
            planoComercial: $plano,
            actor: $actor,
        );

        $cancelada = app(SubscriptionLifecycleService::class)->cancel(
            assinatura: $assinatura,
            reason: 'Encerramento solicitado pelo cliente.',
            actor: $actor,
        );

        $this->assertSame('cancelled', $cancelada->status);
        $this->assertSame('Encerramento solicitado pelo cliente.', $cancelada->cancel_reason);

        $cliente->refresh();
        $this->assertSame('cancelled', $cliente->status);

        $this->assertDatabaseHas('eventos_comerciais_assinante', [
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'event_type' => 'subscription_cancelled',
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
