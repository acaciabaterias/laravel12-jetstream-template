<?php

namespace App\Jobs;

use App\Models\FilaContingencia;
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
                $fiscalGatewayClient->emitirNota($vale, $fila->idempotency_key);
            }

            if ($fila->tipo_integracao === 'bank' && ! $orchestratorIdempotencyService->alreadyProcessedBank($fila->idempotency_key)) {
                $bankGatewayClient->emitirBoleto($vale, $fila->idempotency_key);
            }

            $fila->update([
                'status' => 'processado',
                'tentativas' => $fila->tentativas + 1,
                'ultimo_erro' => null,
            ]);
        } catch (\Throwable $exception) {
            $fila->update([
                'status' => 'pendente',
                'tentativas' => $fila->tentativas + 1,
                'ultimo_erro' => $exception->getMessage(),
                'proxima_tentativa' => now()->addMinutes(10),
            ]);
        }
    }
}
