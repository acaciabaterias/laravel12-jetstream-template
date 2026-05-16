<?php

namespace App\Services\Contracts\Microservices\V1;

use App\Models\CertificadoDigital;
use App\Models\Vale;

class BankSlipPayloadV1
{
    public static function fromVale(Vale $vale, CertificadoDigital $certificado, string $idempotencyKey): array
    {
        return [
            'tenant_id' => $vale->cliente_id,
            'vale_id' => $vale->id,
            'idempotency_key' => $idempotencyKey,
            'valor' => (float) $vale->valor_total,
            'vencimento' => now()->addDays(7)->toDateString(),
            'sacado' => [
                'documento' => preg_replace('/\D+/', '', (string) $vale->cliente?->cnpj),
                'nome' => $vale->cliente?->razao_social,
                'email' => $vale->cliente?->email_contato,
            ],
            'certificado' => [
                'conteudo' => $certificado->conteudo_certificado,
                'senha' => $certificado->senha_certificado,
                'formato' => $certificado->formato,
                'modelo' => $certificado->modelo,
            ],
        ];
    }
}
