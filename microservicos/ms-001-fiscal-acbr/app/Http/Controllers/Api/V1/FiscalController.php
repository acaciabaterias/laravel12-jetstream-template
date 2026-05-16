<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelFiscalRequest;
use App\Http\Requests\CartaCorrecaoRequest;
use App\Http\Requests\EmitirFiscalRequest;
use App\Http\Requests\InutilizarFiscalRequest;
use App\Models\AuditLog;
use App\Models\ContingenciaQueue;
use App\Models\NotaFiscalJob;
use App\Services\AcbrService;
use Illuminate\Http\JsonResponse;

class FiscalController extends Controller
{
    public function __construct(private readonly AcbrService $acbrService) {}

    public function emitirNfe(EmitirFiscalRequest $request): JsonResponse
    {
        return $this->emitir($request->validated(), 'NFe');
    }

    public function emitirNfce(EmitirFiscalRequest $request): JsonResponse
    {
        return $this->emitir($request->validated(), 'NFCe');
    }

    public function cancelar(CancelFiscalRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $response = $this->acbrService->cancelar($payload['chave_acesso'], $payload['justificativa']);

        return response()->json($response);
    }

    public function cce(CartaCorrecaoRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $response = $this->acbrService->cartaCorrecao($payload['chave_acesso'], $payload['correcao']);

        return response()->json($response);
    }

    public function inutilizar(InutilizarFiscalRequest $request): JsonResponse
    {
        return response()->json($this->acbrService->inutilizar($request->validated()));
    }

    public function consultar(string $chaveAcesso): JsonResponse
    {
        return response()->json($this->acbrService->consultar($chaveAcesso));
    }

    public function filaContingencia(): JsonResponse
    {
        return response()->json([
            'data' => ContingenciaQueue::query()->latest('id')->get(),
        ]);
    }

    public function certificadoStatus(): JsonResponse
    {
        return response()->json($this->acbrService->certificadoStatus());
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'service' => 'ms-001-fiscal-acbr',
            'status' => 'ok',
            'acbr' => $this->acbrService->statusServico(),
        ]);
    }

    protected function emitir(array $payload, string $tipo): JsonResponse
    {
        $existingJob = NotaFiscalJob::query()
            ->where('correlation_id', $payload['correlation_id'])
            ->where('status', 'authorized')
            ->first();

        if ($existingJob) {
            return response()->json([
                'status' => 'authorized',
                'cached' => true,
                'job_id' => $existingJob->id,
                'chave' => $existingJob->chave_acesso,
                'protocolo' => $existingJob->protocolo,
            ]);
        }

        $job = NotaFiscalJob::query()->create([
            'vale_id' => $payload['vale_id'],
            'tipo' => $tipo,
            'payload' => [...$payload, 'tipo' => $tipo],
            'status' => 'pending',
            'correlation_id' => $payload['correlation_id'],
        ]);

        try {
            $response = $this->acbrService->emitir([...$payload, 'tipo' => $tipo]);

            $job->update([
                'status' => $response['status'],
                'xml_assinado' => $response['xml'] ?? null,
                'chave_acesso' => $response['chave'] ?? null,
                'protocolo' => $response['protocolo'] ?? null,
            ]);

            AuditLog::query()->create([
                'nota_id' => $job->id,
                'acao' => 'EMISSAO_SUCCESS',
                'payload_entrada' => $payload,
                'payload_saida' => $response,
                'status_http' => 200,
            ]);

            return response()->json([
                'status' => $response['status'],
                'job_id' => $job->id,
                'chave' => $response['chave'] ?? null,
                'protocolo' => $response['protocolo'] ?? null,
                'danfe_url' => $response['danfe_url'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            $job->update([
                'status' => 'contingency',
                'tentativas' => 1,
                'proxima_tentativa' => now()->addMinute(),
            ]);

            ContingenciaQueue::query()->create([
                'nota_id' => $job->id,
                'motivo' => $exception->getMessage(),
                'tentativas_realizadas' => 1,
                'ultima_tentativa' => now(),
                'proxima_tentativa' => now()->addMinute(),
                'status' => 'pending',
            ]);

            AuditLog::query()->create([
                'nota_id' => $job->id,
                'acao' => 'EMISSAO_CONTINGENCIA',
                'payload_entrada' => $payload,
                'payload_saida' => ['error' => $exception->getMessage()],
                'status_http' => 202,
            ]);

            return response()->json([
                'status' => 'contingency',
                'job_id' => $job->id,
                'message' => 'Nota enviada para fila de contingencia.',
            ], 202);
        }
    }
}
