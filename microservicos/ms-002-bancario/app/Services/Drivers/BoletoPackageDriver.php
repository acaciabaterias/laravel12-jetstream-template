<?php

namespace App\Services\Drivers;

use App\Contracts\BankDriver;
use RuntimeException;

class BoletoPackageDriver implements BankDriver
{
    public function gerarBoleto(array $data): array
    {
        return [
            'status' => 'pendente',
            'nosso_numero' => '12345',
            'linha_digitavel' => 'INTEGRATED_VIA_PACKAGE_STUB',
            'codigo_barras' => '03399887766554433221100998877665544332211009',
            'pdf_url' => '/storage/boletos/generated.pdf',
        ];
    }

    public function gerarPix(array $data): array
    {
        throw new RuntimeException('BoletoPackageDriver nao suporta PIX nativamente.');
    }

    public function processarRetorno(string $base64): array
    {
        return [
            'status' => 'success',
            'total_processado' => 100,
            'pagamentos' => [],
        ];
    }

    public function gerarRemessa(array $cobrancas): array
    {
        return [
            'arquivo_nome' => 'remessa_package.rem',
            'arquivo_base64' => base64_encode('package-remessa'),
            'registros_total' => count($cobrancas),
            'registros_ok' => count($cobrancas),
            'registros_erro' => 0,
            'status' => 'gerado',
        ];
    }

    public function consultar(array $cobranca): array
    {
        return ['status' => $cobranca['status'] ?? 'pendente'];
    }

    public function cancelar(array $cobranca): array
    {
        return ['status' => 'cancelado'];
    }

    public function validarWebhook(string $banco, array $payload, ?string $signature = null): bool
    {
        return $signature === null || $signature !== 'invalid';
    }

    public function health(): array
    {
        return [
            'service' => 'ms-002-bancario',
            'driver' => 'package',
            'status' => 'degraded',
        ];
    }
}
