<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EventoComercialAssinante;
use App\Models\PlanoComercial;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformBillingPlanChangeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runCentralBillingMigrations();
    }

    public function test_it_changes_the_subscription_plan_and_preserves_commercial_history(): void
    {
        $cliente = Cliente::factory()->create([
            'status' => 'active',
        ]);
        $planoInicial = PlanoComercial::factory()->create([
            'slug' => 'essential',
        ]);
        $planoNovo = PlanoComercial::factory()->create([
            'slug' => 'enterprise',
        ]);
        $actor = UsuarioPlataforma::factory()->billing()->create();
        $service = app(SubscriptionLifecycleService::class);

        $assinatura = $service->activate(
            cliente: $cliente,
            planoComercial: $planoInicial,
            actor: $actor,
        );

        $alterada = $service->changePlan(
            assinatura: $assinatura,
            novoPlano: $planoNovo,
            attributes: [
                'reason' => 'Upgrade comercial.',
            ],
            actor: $actor,
        );

        $this->assertSame($planoNovo->id, $alterada->plano_id);

        $cliente->refresh();
        $this->assertSame('enterprise', $cliente->plano);
        $this->assertSame($planoNovo->id, $cliente->plano_atual_id);

        $this->assertDatabaseHas('eventos_comerciais_assinante', [
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'event_type' => 'plan_changed',
            'reason' => 'Upgrade comercial.',
        ], 'central');

        $evento = EventoComercialAssinante::query()
            ->where('assinatura_id', $assinatura->id)
            ->where('event_type', 'plan_changed')
            ->first();

        $this->assertNotNull($evento);
        $this->assertSame($planoInicial->id, $evento->before_state['plano_id']);
        $this->assertSame($planoNovo->id, $evento->after_state['plano_id']);
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
