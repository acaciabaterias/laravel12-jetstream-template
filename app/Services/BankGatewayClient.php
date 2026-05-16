<?php

namespace App\Services;

use App\Models\Vale;
use App\Services\Contracts\Microservices\V1\BankSlipPayloadV1;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class BankGatewayClient extends BaseInternalClient
{
    public function __construct(private readonly CertificadoDigitalRepository $certificadoDigitalRepository) {}

    public function emitirBoleto(Vale $vale, string $idempotencyKey): array
    {
        $certificado = $this->certificadoDigitalRepository->obterAtivoPorFinalidade($vale->cliente_id, 'bancario');

        if (! $certificado) {
            throw new RuntimeException('Nenhum certificado digital ativo encontrado para finalidade bancária deste assinante.');
        }

        $payload = BankSlipPayloadV1::fromVale($vale, $certificado, $idempotencyKey);

        try {
            $response = $this->client()
                ->timeout(30)
                ->post(rtrim((string) config('services.ms_bancario.url'), '/').'/api/v1/boleto', $payload)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new RuntimeException('Falha ao emitir boleto no microserviço bancário: '.$exception->getMessage(), 0, $exception);
        }

        return [
            'status' => $response['status'] ?? 'emitido',
            'nosso_numero' => $response['nosso_numero'] ?? null,
            'linha_digitavel' => $response['linha_digitavel'] ?? null,
            'pdf_url' => $response['pdf_url'] ?? null,
            'identificador_externo' => $response['identificador_externo'] ?? ('boleto-'.$idempotencyKey),
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
