<?php

namespace App\Services\Drivers;

use App\Contracts\AcbrDriver;

class AcbrMockDriver implements AcbrDriver
{
    public function emitir(array $data): array
    {
        $tipo = strtoupper($data['tipo'] ?? 'NFE');

        return [
            'status' => 'authorized',
            'tipo' => $tipo,
            'chave' => str_pad((string) ($data['vale_id'] ?? 0), 44, '1', STR_PAD_LEFT),
            'protocolo' => '135'.str_pad((string) rand(1000, 9999), 12, '0', STR_PAD_LEFT),
            'xml' => '<xml tipo="'.$tipo.'">mock</xml>',
            'danfe_url' => 'https://mock.local/danfe/'.($data['vale_id'] ?? '0').'.pdf',
        ];
    }

    public function cancelar(string $chave, string $justificativa): array
    {
        return [
            'status' => 'cancelled',
            'chave' => $chave,
            'justificativa' => $justificativa,
        ];
    }

    public function statusServico(): array
    {
        return [
            'status' => 'ok',
            'driver' => 'mock',
            'sefaz' => 'available',
        ];
    }

    public function consultar(string $chave): array
    {
        return [
            'status' => 'authorized',
            'chave' => $chave,
        ];
    }

    public function cartaCorrecao(string $chave, string $correcao): array
    {
        return [
            'status' => 'accepted',
            'chave' => $chave,
            'correcao' => $correcao,
        ];
    }

    public function inutilizar(array $payload): array
    {
        return [
            'status' => 'accepted',
            'payload' => $payload,
        ];
    }

    public function certificadoStatus(): array
    {
        return [
            'status' => 'valid',
            'dias_restantes' => 90,
        ];
    }
}
