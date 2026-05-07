<?php

namespace App\Services\Contracts\Microservices\V1;

use App\Models\CertificadoDigital;
use App\Models\Vale;

class FiscalEmissionPayloadV1
{
    public static function fromVale(Vale $vale, CertificadoDigital $certificado, string $correlationId): array
    {
        return [
            'tenant_id' => $vale->cliente_id,
            'vale_id' => $vale->id,
            'correlation_id' => $correlationId,
            'tipo' => 'nfe',
            'emitente' => [
                'cnpj' => preg_replace('/\D+/', '', (string) $vale->cliente?->cnpj),
                'razao_social' => $vale->cliente?->razao_social,
            ],
            'itens' => $vale->itens->map(fn ($item) => [
                'descricao' => $item->bateria?->modelo ?? 'Item',
                'quantidade' => (int) $item->quantidade,
                'valor_unitario' => (float) $item->preco_unitario_final,
            ])->values()->all(),
            'certificado' => [
                'conteudo' => $certificado->conteudo_certificado,
                'senha' => $certificado->senha_certificado,
                'formato' => $certificado->formato,
                'modelo' => $certificado->modelo,
            ],
        ];
    }
}
