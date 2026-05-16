<?php

namespace App\Services;

class BankApiClient
{
    public function fetchTransactions(): array
    {
        return [
            [
                'identificador_externo' => 'bank-tx-001',
                'tipo' => 'credito',
                'valor' => 250.00,
                'data_transacao' => now()->subDay()->toDateTimeString(),
                'descricao' => 'Recebimento PIX pedido',
            ],
            [
                'identificador_externo' => 'bank-tx-002',
                'tipo' => 'debito',
                'valor' => 180.00,
                'data_transacao' => now()->subDay()->toDateTimeString(),
                'descricao' => 'Cobranca garantia improcedente',
            ],
        ];
    }
}
