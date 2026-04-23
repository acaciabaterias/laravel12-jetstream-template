<?php

namespace App\Services;

use App\Models\Vale;

class FiscalGatewayClient
{
    public function emitirNota(Vale $vale, string $idempotencyKey): array
    {
        return [
            'status' => 'emitida',
            'chave_acesso' => 'NFE'.str_pad((string) $vale->id, 8, '0', STR_PAD_LEFT),
            'xml_path' => 'generated://nfe/'.$vale->id.'.xml',
            'ms_requisicao_id' => 'fiscal-'.$idempotencyKey,
        ];
    }
}
