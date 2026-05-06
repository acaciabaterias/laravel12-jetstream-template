<?php

namespace Tests\Feature;

use App\Http\Middleware\PrometheusMetrics;
use App\Http\Middleware\TenantConnectionMiddleware;
use App\Jobs\DispatchCnabProcessingJob;
use App\Jobs\RetryOrchestratorJob;
use App\Livewire\CnabUploadPanel;
use App\Models\BoletoOrquestrado;
use App\Models\CertificadoDigital;
use App\Models\Cliente;
use App\Models\CnabRetornoUpload;
use App\Models\FilaContingencia;
use App\Models\Filial;
use App\Models\NotaFiscalOrquestrada;
use App\Models\User;
use App\Models\Vale;
use App\Services\BankGatewayClient;
use App\Services\CnabOrchestratorService;
use App\Services\FiscalGatewayClient;
use App\Services\OrchestratorIdempotencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class FiscalBankOrchestratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PrometheusMetrics::class);

        Route::middleware('web')->get('/orchestrator/tenant-probe', function (Request $request) {
            return response()->json([
                'tenant_host' => config('database.connections.tenant.host'),
                'cliente_id' => optional($request->attributes->get('cliente'))->id,
            ]);
        });
    }

    public function test_retry_processes_contingency_after_external_failure(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
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

    public function test_retry_persists_certificate_metadata_for_fiscal_and_bank_documents(): void
    {
        Artisan::call('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central/2026_05_06_114540_create_certificados_digitais_table.php',
            '--force' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_05_06_115908_add_certificate_metadata_to_orchestrated_tables.php',
            '--force' => true,
        ]);

        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
        $vale = Vale::query()->create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $user->id,
            'status' => 'faturado',
            'data_criacao' => now(),
            'data_faturamento' => now(),
            'created_by' => $user->id,
        ]);

        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'fiscal',
            'nome_referencia' => 'CERT-FISCAL-ERP',
            'status' => 'active',
        ]);
        CertificadoDigital::factory()->create([
            'cliente_id' => $cliente->id,
            'finalidade' => 'bancario',
            'nome_referencia' => 'CERT-BANK-ERP',
            'status' => 'active',
        ]);

        $service = app(OrchestratorIdempotencyService::class);
        Http::fake([
            '*/api/v1/nfe/emitir' => Http::response([
                'status' => 'emitida',
                'chave_acesso' => 'NFE-TST-1',
                'xml_autorizado' => 'generated://nfe/teste.xml',
                'correlation_id' => 'corr-fiscal',
            ], 200),
            '*/api/v1/boleto' => Http::response([
                'status' => 'emitido',
                'nosso_numero' => 'NN-TST-1',
                'linha_digitavel' => '34191.79001',
                'pdf_url' => 'https://bank.local/boleto/teste.pdf',
                'identificador_externo' => 'boleto-corr-bank',
            ], 200),
        ]);

        $filaFiscal = FilaContingencia::query()->create([
            'tipo_integracao' => 'fiscal',
            'payload' => ['vale_id' => $vale->id],
            'tentativas' => 0,
            'status' => 'pendente',
            'idempotency_key' => $service->forFiscal($vale).'-cert',
        ]);

        $filaBank = FilaContingencia::query()->create([
            'tipo_integracao' => 'bank',
            'payload' => ['vale_id' => $vale->id],
            'tentativas' => 0,
            'status' => 'pendente',
            'idempotency_key' => $service->forBank($vale).'-cert',
        ]);

        (new RetryOrchestratorJob($filaFiscal->id))->handle(
            app(FiscalGatewayClient::class),
            app(BankGatewayClient::class),
            app(OrchestratorIdempotencyService::class),
        );

        (new RetryOrchestratorJob($filaBank->id))->handle(
            app(FiscalGatewayClient::class),
            app(BankGatewayClient::class),
            app(OrchestratorIdempotencyService::class),
        );

        $this->assertDatabaseHas('notas_fiscais_orquestradas', [
            'idempotency_key' => $filaFiscal->idempotency_key,
            'certificado_referencia' => 'CERT-FISCAL-ERP',
        ]);

        $this->assertDatabaseHas('boletos_orquestrados', [
            'idempotency_key' => $filaBank->idempotency_key,
            'certificado_referencia' => 'CERT-BANK-ERP',
        ]);
        Http::assertSentCount(2);
    }

    public function test_idempotency_blocks_duplicate_orchestrated_documents(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
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
        $cliente = Cliente::factory()->create();
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

        (new DispatchCnabProcessingJob($upload->id))->handle(app(CnabOrchestratorService::class));

        $this->assertDatabaseHas('cnab_retorno_uploads', [
            'id' => $upload->id,
            'status_processamento' => 'processado',
        ]);
    }

    public function test_cnab_panel_registers_remessa_with_download_path_and_tracking_status(): void
    {
        $user = User::factory()->withPersonalTeam()->create(['papel' => 'gestor', 'ativo' => true]);
        $this->actingAs($user);

        Livewire::test(CnabUploadPanel::class)
            ->set('tipoArquivo', 'remessa')
            ->set('nomeRemessa', 'remessa_20260506.rem')
            ->call('registerRemessa')
            ->assertHasNoErrors()
            ->assertSee('remessa_20260506.rem')
            ->assertSee('Gerada')
            ->assertSee('/storage/cnab/remessas/remessa_20260506.rem');

        $this->assertDatabaseHas('cnab_remessas', [
            'nome_arquivo' => 'remessa_20260506.rem',
            'status' => 'gerada',
            'arquivo_path' => '/storage/cnab/remessas/remessa_20260506.rem',
        ]);
    }

    public function test_dashboard_renders_orchestrator_components_for_finance_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'gestor',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSeeLivewire('fiscal-contingency-dashboard')
            ->assertSeeLivewire('cnab-upload-panel');
    }

    public function test_dashboard_hides_orchestrator_components_for_non_finance_roles(): void
    {
        $filial = Filial::factory()->create();
        $user = User::factory()->withPersonalTeam()->create([
            'papel' => 'entregador',
            'ativo' => true,
            'filial_id' => $filial->id,
        ]);
        $this->actingAs($user);

        $this->withoutMiddleware(TenantConnectionMiddleware::class);

        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertDontSeeLivewire('fiscal-contingency-dashboard')
            ->assertDontSeeLivewire('cnab-upload-panel');
    }

    public function test_retry_failure_uses_controlled_backoff_and_escalates_to_critical(): void
    {
        $user = User::factory()->create(['papel' => 'gestor', 'ativo' => true]);
        $cliente = Cliente::factory()->create();
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
            'idempotency_key' => app(OrchestratorIdempotencyService::class)->forFiscal($vale).'-retry',
        ]);

        $fiscalMock = Mockery::mock(FiscalGatewayClient::class);
        $fiscalMock->shouldReceive('emitirNota')->andThrow(new \RuntimeException('falha externa'));
        $bankMock = Mockery::mock(BankGatewayClient::class);
        $bankMock->shouldReceive('emitirBoleto')->andReturn([]);

        (new RetryOrchestratorJob($fila->id))->handle(
            $fiscalMock,
            $bankMock,
            app(OrchestratorIdempotencyService::class),
        );

        $fila->refresh();
        $this->assertSame(1, $fila->tentativas);
        $this->assertSame('pendente', $fila->status);
        $this->assertTrue(now()->diffInMinutes($fila->proxima_tentativa, false) <= 1);

        $fila->update(['tentativas' => 9, 'status' => 'pendente']);

        (new RetryOrchestratorJob($fila->id))->handle(
            $fiscalMock,
            $bankMock,
            app(OrchestratorIdempotencyService::class),
        );

        $fila->refresh();
        $this->assertSame(10, $fila->tentativas);
        $this->assertSame('critico', $fila->status);
    }

    public function test_orchestrator_requests_are_isolated_between_tenants_without_cross_access(): void
    {
        $this->withoutMiddleware(PrometheusMetrics::class);

        $tenantA = Cliente::factory()->create([
            'subdominio' => 'orc-a',
            'status' => 'active',
            'supabase_db_host' => 'db-orc-a.supabase.co',
        ]);
        $tenantB = Cliente::factory()->create([
            'subdominio' => 'orc-b',
            'status' => 'active',
            'supabase_db_host' => 'db-orc-b.supabase.co',
        ]);

        $responseA = $this->get('http://orc-a.erp.com/orchestrator/tenant-probe');
        $responseB = $this->get('http://orc-b.erp.com/orchestrator/tenant-probe');

        $responseA->assertOk()->assertJson([
            'tenant_host' => 'db-orc-a.supabase.co',
            'cliente_id' => $tenantA->id,
        ]);

        $responseB->assertOk()->assertJson([
            'tenant_host' => 'db-orc-b.supabase.co',
            'cliente_id' => $tenantB->id,
        ]);
    }
}
