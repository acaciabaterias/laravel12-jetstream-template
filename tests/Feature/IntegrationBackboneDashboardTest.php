<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Http\Middleware\TenantConnectionMiddleware;
use App\Livewire\IntegrationBackboneDashboard;
use App\Models\ContratoEvento;
use App\Models\EventoOutbox;
use App\Models\Filial;
use App\Models\User;
use App\Services\Integration\OutboxEventFactory;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class IntegrationBackboneDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PrometheusMetrics::class);

        Schema::connection('tenant')->dropIfExists('endpoints_integracao');
        Schema::connection('tenant')->dropIfExists('contratos_evento');
        Schema::connection('tenant')->dropIfExists('entregas_integracao');
        Schema::connection('tenant')->dropIfExists('evento_inboxes');
        Schema::connection('tenant')->dropIfExists('evento_outboxes');

        $this->artisan('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant/2026_05_06_204458_create_integration_backbone_tables.php',
            '--realpath' => false,
        ])->assertExitCode(0);
    }

    public function test_dashboard_renders_backbone_component_for_operational_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);

        EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'VALE_FATURADO',
                payload: ['vale_id' => 1],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'dashboard-1',
                correlationId: 'ca8a0e1d-b5ad-4a9e-88e9-77c570320f1d',
            )
        );
        ContratoEvento::query()->create([
            'event_type' => 'VALE_FATURADO',
            'event_version' => 'v1',
            'producer' => 'sales',
            'status' => 'active',
            'consumers' => ['ms-001'],
        ]);

        $this->actingAs($user);
        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSeeLivewire('integration-backbone-dashboard');
    }

    public function test_dashboard_hides_backbone_component_for_non_operational_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'vendedor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);

        $this->actingAs($user);
        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertDontSeeLivewire('integration-backbone-dashboard');
    }

    public function test_livewire_dashboard_filters_outbox_by_event_type(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($user);

        EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'VALE_FATURADO',
                payload: ['vale_id' => 1],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'filter-1',
                correlationId: 'f2cc600c-5e39-4636-83d2-06ea31780e31',
            )
        );
        EventoOutbox::query()->create(
            app(OutboxEventFactory::class)->make(
                eventType: 'COBRANCA_CRIAR_BOLETO',
                payload: ['vale_id' => 2],
                tenantExternalRef: 'tenant-a',
                idempotencyKey: 'filter-2',
                correlationId: '14e3e2bf-e57b-43c6-9178-695dbd7ad45b',
                eventVersion: 'v1',
            )
        );

        Livewire::test(IntegrationBackboneDashboard::class)
            ->set('eventTypeFilter', 'VALE_FATURADO')
            ->assertSee('VALE_FATURADO')
            ->assertDontSee('COBRANCA_CRIAR_BOLETO');
    }
}
