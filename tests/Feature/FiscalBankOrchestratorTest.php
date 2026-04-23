<?php

namespace Tests\Feature;

use App\Jobs\DispatchCnabProcessingJob;
use App\Jobs\RetryOrchestratorJob;
use App\Livewire\CnabUploadPanel;
use App\Models\BoletoOrquestrado;
use App\Models\CnabRetornoUpload;
use App\Models\FilaContingencia;
use App\Models\NotaFiscalOrquestrada;
use App\Models\User;
use App\Models\Vale;
use App\Services\BankGatewayClient;
use App\Services\FiscalGatewayClient;
use App\Services\OrchestratorIdempotencyService;
use Livewire\Livewire;
use Tests\TestCase;

class FiscalBankOrchestratorTest extends TestCase
{
    public function test_retry_processes_contingency_after_external_failure(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = \App\Models\Cliente::factory()->create();
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'data_faturamento' => now(),
            'created_by' => $user->id,
        ]);

        $fila = FilaContingencia::query()->create([
            'tipo_integracao' => 'fiscal',
            'payload' => ['vale_id' => $vale->id],
            'tentativas' => 0,
            'status' => 'pendente',
            'idempotency_key' => app(OrchestratorIdempotencyService::class)->forFiscal($vale),
        ]);

        NotaFiscalOrquestrada::query()->create([
            'vale_id' => $vale->id,
            'status' => 'emitida',
            'idempotency_key' => $fila->idempotency_key,
        ]);

        (new RetryOrchestratorJob($fila->id))->handle(
            app(FiscalGatewayClient::class),
            app(BankGatewayClient::class),
            app(OrchestratorIdempotencyService::class),
        );

        $this->assertDatabaseHas('filas_contingencia', [
            'id' => $fila->id,
            'status' => 'processado',
        ]);
    }

    public function test_idempotency_blocks_duplicate_orchestrated_documents(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = \App\Models\Cliente::factory()->create();
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'data_faturamento' => now(),
            'created_by' => $user->id,
        ]);

        $service = app(OrchestratorIdempotencyService::class);
        $key = $service->forBank($vale);

        BoletoOrquestrado::query()->create([
            'vale_id' => $vale->id,
            'status' => 'emitido',
            'idempotency_key' => $key,
        ]);

        $this->assertTrue($service->alreadyProcessedBank($key));
    }

    public function test_invalid_cnab_upload_does_not_break_panel(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($user);

        Livewire::test(CnabUploadPanel::class)
            ->set('nomeArquivo', 'arquivo_invalido.txt')
            ->call('registerUpload')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cnab_retorno_uploads', [
            'nome_arquivo' => 'arquivo_invalido.txt',
            'status_processamento' => 'erro',
        ]);
    }

    public function test_successful_cnab_processing_updates_upload_status(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = \App\Models\Cliente::factory()->create();
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'data_faturamento' => now(),
            'created_by' => $user->id,
        ]);
        BoletoOrquestrado::query()->create([
            'vale_id' => $vale->id,
            'nosso_numero' => 'NN00000001',
            'status' => 'emitido',
            'idempotency_key' => 'bank-key-1',
        ]);
        $upload = CnabRetornoUpload::query()->create([
            'nome_arquivo' => 'retorno.ret',
            'status_processamento' => 'pendente',
        ]);

        (new DispatchCnabProcessingJob($upload->id))->handle(app(\App\Services\CnabOrchestratorService::class));

        $this->assertDatabaseHas('cnab_retorno_uploads', [
            'id' => $upload->id,
            'status_processamento' => 'processado',
        ]);
    }

    public function test_dashboard_renders_orchestrator_components_for_finance_roles(): void
    {
        $filial = \App\Models\Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeLivewire('fiscal-contingency-dashboard')
            ->assertSeeLivewire('cnab-upload-panel');
    }

    public function test_dashboard_hides_orchestrator_components_for_non_finance_roles(): void
    {
        $filial = \App\Models\Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'entregador',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertDontSeeLivewire('fiscal-contingency-dashboard')
            ->assertDontSeeLivewire('cnab-upload-panel');
    }
}
