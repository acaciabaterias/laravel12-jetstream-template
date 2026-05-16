<?php

namespace App\Services\Drivers;

use App\Contracts\BankDriver;
use Illuminate\Support\Str;

class MockBankDriver implements BankDriver
{
    public function gerarBoleto(array $data): array
    {
        return [
            'status' => 'pendente',
            'nosso_numero' => rand(100000, 999999),
            'linha_digitavel' => '00190.50095 40144.816069 06809.350314 3 0000000000',
            'codigo_barras' => '00193373700000001000500940144816060680935031',
            'pdf_url' => 'http://localhost:8002/storage/boletos/mocked.pdf',
        ];
    }

    public function gerarPix(array $data): array
    {
        return [
            'status' => 'pendente',
            'txid' => Str::random(25),
            'qr_code_string' => '00020101021226850014br.gov.bcb.pix0123mock-pix-key-1234567890520400005303986540510.005802BR5913BATERIAEXPERT6009SAOPAULO62070503***6304abcd',
            'qr_code_imagem_base64' => base64_encode('mock-qr-code'),
            'link_pagamento' => 'https://pix.bateria-expert.com/pay/'.Str::random(10),
        ];
    }

    public function processarRetorno(string $base64): array
    {
        return [
            'status' => 'success',
            'total_processado' => 10,
            'pagamentos' => [
                ['nosso_numero' => '123456', 'status' => 'pago', 'valor' => 500.00],
                ['nosso_numero' => '789012', 'status' => 'baixado', 'valor' => 0.00],
            ],
        ];
    }

    public function gerarRemessa(array $cobrancas): array
    {
        return [
            'arquivo_nome' => 'remessa_'.now()->format('Ymd_His').'.rem',
            'arquivo_base64' => base64_encode(json_encode($cobrancas)),
            'registros_total' => count($cobrancas),
            'registros_ok' => count($cobrancas),
            'registros_erro' => 0,
            'status' => 'gerado',
        ];
    }

    public function consultar(array $cobranca): array
    {
        return [
            'status' => $cobranca['status'] ?? 'pendente',
            'pago_em' => null,
        ];
    }

    public function cancelar(array $cobranca): array
    {
        return [
            'status' => 'cancelled',
            'protocolo' => 'MOCK-BP-'.rand(1000, 9999),
        ];
    }

    public function validarWebhook(string $banco, array $payload, ?string $signature = null): bool
    {
        return $signature === null || $signature !== 'invalid';
    }

    public function health(): array
    {
        return [
            'service' => 'ms-002-bancario',
            'driver' => 'mock',
            'status' => 'ok',
        ];
    }
}
