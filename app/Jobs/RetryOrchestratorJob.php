<?php

namespace App\Jobs;

use App\Models\BoletoOrquestrado;
use App\Models\FilaContingencia;
use App\Models\NotaFiscalOrquestrada;
use App\Models\Vale;
use App\Services\BankGatewayClient;
use App\Services\FiscalGatewayClient;
use App\Services\OrchestratorIdempotencyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetryOrchestratorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $filaContingenciaId) {}

    public function handle(
        FiscalGatewayClient $fiscalGatewayClient,
        BankGatewayClient $bankGatewayClient,
        OrchestratorIdempotencyService $orchestratorIdempotencyService,
    ): void {
        $fila = FilaContingencia::query()->findOrFail($this->filaContingenciaId);
        $vale = Vale::query()->findOrFail($fila->payload['vale_id']);

        try {
            if ($fila->tipo_integracao === 'fiscal' && ! $orchestratorIdempotencyService->alreadyProcessedFiscal($fila->idempotency_key)) {
                $resultadoFiscal = $fiscalGatewayClient->emitirNota($vale, $fila->idempotency_key);
                NotaFiscalOrquestrada::query()->updateOrCreate(
                    ['idempotency_key' => $fila->idempotency_key],
                    [
                        'vale_id' => $vale->id,
                        'status' => $resultadoFiscal['status'] ?? 'emitida',
                        'chave_acesso' => $resultadoFiscal['chave_acesso'] ?? null,
                        'xml_path' => $resultadoFiscal['xml_path'] ?? null,
                        'ms_requisicao_id' => $resultadoFiscal['ms_requisicao_id'] ?? null,
                        'certificado_digital_id' => $resultadoFiscal['certificado']['id'] ?? null,
                        'certificado_referencia' => $resultadoFiscal['certificado']['referencia'] ?? null,
                    ]
                );
            }

            if ($fila->tipo_integracao === 'bank' && ! $orchestratorIdempotencyService->alreadyProcessedBank($fila->idempotency_key)) {
                $resultadoBank = $bankGatewayClient->emitirBoleto($vale, $fila->idempotency_key);
                BoletoOrquestrado::query()->updateOrCreate(
                    ['idempotency_key' => $fila->idempotency_key],
                    [
                        'vale_id' => $vale->id,
                        'status' => $resultadoBank['status'] ?? 'emitido',
                        'nosso_numero' => $resultadoBank['nosso_numero'] ?? null,
                        'linha_digitavel' => $resultadoBank['linha_digitavel'] ?? null,
                        'pdf_url' => $resultadoBank['pdf_url'] ?? null,
                        'identificador_externo' => $resultadoBank['identificador_externo'] ?? null,
                        'certificado_digital_id' => $resultadoBank['certificado']['id'] ?? null,
                        'certificado_referencia' => $resultadoBank['certificado']['referencia'] ?? null,
                    ]
                );
            }

            $fila->update([
                'status' => 'processado',
                'tentativas' => $fila->tentativas + 1,
                'ultimo_erro' => null,
            ]);
        } catch (\Throwable $exception) {
            $tentativas = $fila->tentativas + 1;
            $minutosBackoff = $this->resolverBackoffMinutos($tentativas);

            $fila->update([
                'status' => $tentativas >= 10 ? 'critico' : 'pendente',
                'tentativas' => $tentativas,
                'ultimo_erro' => $exception->getMessage(),
                'proxima_tentativa' => now()->addMinutes($minutosBackoff),
            ]);
        }
    }

    protected function resolverBackoffMinutos(int $tentativas): int
    {
        return match (true) {
            $tentativas <= 1 => 1,
            $tentativas === 2 => 5,
            $tentativas === 3 => 30,
            $tentativas === 4 => 120,
            default => 360,
        };
    }
}
