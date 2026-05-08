<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Models\PlanoComercial;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\ExternalChargeIssuanceService;
use App\Services\Billing\PaymentWebhookIngestionService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformPaymentsReplayFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_payments.events.publish_to_backbone', false);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_replay_command_reprocesses_a_return_with_operator_context(): void
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
            'valor' => 210.00,
        ]);
        $gateway = GatewayCobrancaSaaS::factory()->create();
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $cobranca = app(ExternalChargeIssuanceService::class)->issue($fatura, $gateway, 'pix', $operador);
        $retorno = app(PaymentWebhookIngestionService::class)->ingest($gateway, [
            'external_event_id' => 'evt-replay',
            'external_reference' => $cobranca->external_reference,
            'external_charge_id' => $cobranca->external_charge_id,
            'event_type' => 'payment_received',
            'amount' => 210.00,
            'idempotency_key' => 'replay-key',
        ], actor: $operador);

        $retorno->update([
            'processing_status' => 'failed',
            'processing_error' => 'manual_replay_required',
        ]);

        $this->artisan(sprintf('platform-payments:replay-return %d --operator=%d', $retorno->id, $operador->id))
            ->expectsOutputToContain('Replay do retorno')
            ->assertExitCode(0);

        $retorno->refresh();

        $this->assertSame('processed', $retorno->processing_status->value);
        $this->assertDatabaseHas('conciliacoes_pagamento_saas', [
            'retorno_pagamento_saas_id' => $retorno->id,
            'operator_user_id' => $operador->id,
        ], 'central');
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => 'payment_return_replayed',
            'table_name' => 'retornos_pagamento_saas',
            'record_id' => $retorno->id,
        ]);
    }
}
