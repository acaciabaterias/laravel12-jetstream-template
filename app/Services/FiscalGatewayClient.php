<?php

namespace App\Services;

use App\Models\Vale;
use App\Services\Contracts\Microservices\V1\FiscalEmissionPayloadV1;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class FiscalGatewayClient extends BaseInternalClient
{
    public function __construct(private readonly CertificadoDigitalRepository $certificadoDigitalRepository) {}

    public function emitirNota(Vale $vale, string $idempotencyKey): array
    {
        $certificado = $this->certificadoDigitalRepository->obterAtivoPorFinalidade($vale->cliente_id, 'fiscal');

        if (! $certificado) {
            throw new RuntimeException('Nenhum certificado digital ativo encontrado para finalidade fiscal deste assinante.');
        }

        $payload = FiscalEmissionPayloadV1::fromVale($vale, $certificado, $idempotencyKey);

        try {
            $response = $this->client()
                ->timeout(30)
                ->post(rtrim((string) config('services.ms_fiscal.url'), '/').'/api/v1/nfe/emitir', $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new RuntimeException('Falha ao emitir nota no microserviço fiscal: '.$exception->getMessage(), 0, $exception);
        }

        return [
            'status' => $response['status'] ?? 'emitida',
            'chave_acesso' => $response['chave_acesso'] ?? null,
            'xml_path' => $response['xml_autorizado'] ?? ($response['xml_path'] ?? null),
            'ms_requisicao_id' => $response['correlation_id'] ?? $idempotencyKey,
            'certificado' => [
                'id' => $certificado->id,
                'referencia' => $certificado->nome_referencia,
                'modelo' => $certificado->modelo,
                'formato' => $certificado->formato,
            ],
            'payload_ms' => $payload,
        ];
    }
}
