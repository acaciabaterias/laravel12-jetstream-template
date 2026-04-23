<?php

namespace App\Services;

use App\Models\Vale;

class BankGatewayClient
{
    public function emitirBoleto(Vale $vale, string $idempotencyKey): array
    {
        return [
            'status' => 'emitido',
            'nosso_numero' => 'NN'.str_pad((string) $vale->id, 8, '0', STR_PAD_LEFT),
            'linha_digitavel' => '34191.79001 01043.510047 91020.150008 5 12340000025000',
            'pdf_url' => 'https://bank.local/boleto/'.$vale->id.'.pdf',
            'identificador_externo' => 'boleto-'.$idempotencyKey,
        ];
    }
}
