<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformRevenueRecoveryManager;
use App\Models\AcaoRecuperacaoReceita;
use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformRevenueRecoveryPromiseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_revenue_recovery.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_billing_operator_can_record_promise_and_suspend_automated_actions(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $cliente = Cliente::factory()->create(['subdominio' => 'tenant-promise']);
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
        $case = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
        ]);
        $scheduledAction = AcaoRecuperacaoReceita::factory()->create([
            'caso_recuperacao_receita_id' => $case->id,
            'channel' => 'email',
            'status' => 'scheduled',
        ]);

        $this->actingAs($operador, 'platform');

        Livewire::test(PlatformRevenueRecoveryManager::class)
            ->set('casoRecuperacaoReceitaId', (string) $case->id)
            ->set('promisedAmount', '150.50')
            ->set('promisedDate', now()->addDays(2)->toDateString())
            ->set('suspendsUntil', now()->addDays(2)->toDateString())
            ->set('promiseNotes', 'Cliente pediu prazo adicional.')
            ->call('recordPromise')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('compromissos_pagamento', [
            'caso_recuperacao_receita_id' => $case->id,
            'recorded_by_user_id' => $operador->id,
            'status' => 'open',
        ], 'central');
        $this->assertDatabaseHas('acoes_recuperacao_receita', [
            'id' => $scheduledAction->id,
            'status' => 'skipped',
            'result_code' => 'suspended_by_promise',
        ], 'central');
        $this->assertDatabaseHas('casos_recuperacao_receita', [
            'id' => $case->id,
            'status' => 'paused',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'PROMESSA_PAGAMENTO_REGISTRADA',
            'tenant_external_ref' => 'tenant-promise',
        ], 'central');
    }
}
