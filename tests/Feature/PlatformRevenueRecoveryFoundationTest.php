<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\CompromissoPagamento;
use App\Models\FaturaSaaS;
use App\Models\IndicadorRecuperacaoReceita;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\PlatformRevenueRecoveryEventPublisher;
use App\Support\Billing\PaymentPromiseStatus;
use App\Support\Billing\RevenueRecoveryActionStatus;
use App\Support\Billing\RevenueRecoveryActionType;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoveryPolicyStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformRevenueRecoveryFoundationTest extends TestCase
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

    public function test_central_revenue_recovery_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('politicas_recuperacao_receita'));
        $this->assertTrue(Schema::connection('central')->hasTable('casos_recuperacao_receita'));
        $this->assertTrue(Schema::connection('central')->hasTable('acoes_recuperacao_receita'));
        $this->assertTrue(Schema::connection('central')->hasTable('compromissos_pagamento'));
        $this->assertTrue(Schema::connection('central')->hasTable('indicadores_recuperacao_receita'));
    }

    public function test_revenue_recovery_models_persist_relationships_and_enum_casts(): void
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
        $owner = UsuarioPlataforma::factory()->billing()->create();
        $policy = PoliticaRecuperacaoReceita::factory()->create([
            'status' => RevenueRecoveryPolicyStatus::Active->value,
        ]);
        $caso = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'fatura_saas_id' => $fatura->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'owner_user_id' => $owner->id,
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'severity' => RevenueRecoverySeverity::High->value,
        ]);
        $acao = AcaoRecuperacaoReceita::factory()->create([
            'caso_recuperacao_receita_id' => $caso->id,
            'operator_user_id' => $owner->id,
            'action_type' => RevenueRecoveryActionType::AutomatedReminder->value,
            'status' => RevenueRecoveryActionStatus::Scheduled->value,
        ]);
        $compromisso = CompromissoPagamento::factory()->create([
            'caso_recuperacao_receita_id' => $caso->id,
            'recorded_by_user_id' => $owner->id,
            'status' => PaymentPromiseStatus::Open->value,
        ]);
        $indicador = IndicadorRecuperacaoReceita::factory()->create([
            'open_cases' => 3,
        ]);

        $this->assertSame('central', $policy->getConnectionName());
        $this->assertSame($policy->id, $caso->politica->id);
        $this->assertSame($fatura->id, $caso->fatura->id);
        $this->assertSame($acao->id, $caso->acoes()->firstOrFail()->id);
        $this->assertSame($compromisso->id, $caso->compromissos()->firstOrFail()->id);
        $this->assertSame(RevenueRecoveryPolicyStatus::Active, $policy->status);
        $this->assertSame(RevenueRecoveryCaseStatus::Open, $caso->status);
        $this->assertSame(RevenueRecoverySeverity::High, $caso->severity);
        $this->assertSame(RevenueRecoveryActionType::AutomatedReminder, $acao->action_type);
        $this->assertSame(RevenueRecoveryActionStatus::Scheduled, $acao->status);
        $this->assertSame(PaymentPromiseStatus::Open, $compromisso->status);
        $this->assertSame(3, $indicador->open_cases);
    }

    public function test_platform_revenue_recovery_permissions_are_restricted_to_platform_billing_roles(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $support = UsuarioPlataforma::factory()->create();

        $this->assertTrue(Gate::forUser($billing)->allows('manage-platform-revenue-recovery'));
        $this->assertFalse(Gate::forUser($support)->allows('manage-platform-revenue-recovery'));
    }

    public function test_revenue_recovery_event_publisher_creates_contract_and_central_outbox_record(): void
    {
        Queue::fake();

        $cliente = Cliente::factory()->create([
            'subdominio' => 'tenant-recovery',
        ]);
        $plano = PlanoComercial::factory()->create();
        $assinatura = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plano->id,
        ]);
        $fatura = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'referencia' => '2026-06',
        ])->loadMissing('cliente');

        app(PlatformRevenueRecoveryEventPublisher::class)->publish(
            eventType: 'RECUPERACAO_RECEITA_INICIADA',
            faturaSaaS: $fatura,
            payload: [
                'invoice_id' => $fatura->id,
                'tenant_id' => $cliente->id,
                'stage_name' => 'd1',
            ],
            consumers: ['platform', 'ms-003'],
            schemaDefinition: ['invoice_id' => 'integer', 'tenant_id' => 'integer', 'stage_name' => 'string'],
        );

        $this->assertDatabaseHas('contratos_evento', [
            'event_type' => 'RECUPERACAO_RECEITA_INICIADA',
            'producer' => 'platform-revenue-recovery',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'RECUPERACAO_RECEITA_INICIADA',
            'tenant_external_ref' => 'tenant-recovery',
            'origin_context' => 'platform-revenue-recovery',
        ], 'central');
    }
}
