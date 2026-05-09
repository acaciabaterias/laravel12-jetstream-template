<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\EventoOutbox;
use App\Models\ExcecaoConciliacaoSaaS;
use App\Models\FaturaSaaS;
use App\Models\OperationalAlertSnapshot;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\AssinaturaPlataforma;
use App\Services\Integration\IntegrationStorageManager;
use App\Services\Operations\OperationalHealthSnapshotService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProductionObservabilitySnapshotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('production_observability.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
            'database/migrations/central/2026_05_08_220105_create_central_snapshot_analytics_comercials_table.php',
            'database/migrations/central/2026_05_09_133557_create_central_operational_slo_definitions_table.php',
            'database/migrations/central/2026_05_09_133558_create_central_operational_alert_snapshots_table.php',
            'database/migrations/central/2026_05_09_133559_create_central_load_test_baselines_table.php',
            'database/migrations/central/2026_05_09_133600_create_central_operational_incident_records_table.php',
            'database/migrations/central/2026_05_09_133601_create_central_runbook_execution_evidences_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_it_generates_operational_snapshots_and_publishes_degradation_events(): void
    {
        Queue::fake();

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
        $policy = PoliticaRecuperacaoReceita::factory()->create();

        ExcecaoConciliacaoSaaS::factory()->create([
            'fatura_saas_id' => $fatura->id,
            'status' => 'open',
        ]);

        CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'status' => 'open',
            'last_action_at' => now()->subDays(7),
        ]);

        app(IntegrationStorageManager::class)->using('central', function (): void {
            foreach (range(1, 5) as $index) {
                EventoOutbox::query()->create([
                    'event_type' => 'TEST_EVENT',
                    'event_version' => 'v1',
                    'tenant_external_ref' => 'tenant-a',
                    'correlation_id' => (string) fake()->uuid(),
                    'idempotency_key' => fake()->uuid(),
                    'origin_context' => 'tests',
                    'status' => 'dead_letter',
                    'attempts' => 3,
                    'occurred_at' => now(),
                    'payload' => ['test' => true, 'index' => $index],
                    'metadata' => ['source' => 'test'],
                ]);
            }
        });

        $snapshots = app(OperationalHealthSnapshotService::class)->rebuild();

        $this->assertCount(4, $snapshots);
        $this->assertSame(4, OperationalAlertSnapshot::query()->count());
        $this->assertDatabaseHas('operational_alert_snapshots', [
            'flow_name' => 'integration_backbone',
            'severity' => 'warning',
        ], 'central');
        $this->assertDatabaseHas('operational_alert_snapshots', [
            'flow_name' => 'platform_payments',
            'severity' => 'warning',
        ], 'central');
        $this->assertDatabaseHas('operational_alert_snapshots', [
            'flow_name' => 'platform_recovery',
            'severity' => 'warning',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'INCIDENTE_OPERACIONAL_ABERTO',
            'origin_context' => 'production-observability',
        ], 'central');
    }
}
