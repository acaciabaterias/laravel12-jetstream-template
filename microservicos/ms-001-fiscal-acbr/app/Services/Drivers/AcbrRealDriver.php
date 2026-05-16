<?php

namespace App\Services\Drivers;

use App\Contracts\AcbrDriver;
use RuntimeException;

class AcbrRealDriver implements AcbrDriver
{
    public function emitir(array $data): array
    {
        throw new RuntimeException('Driver real do ACBr ainda nao esta conectado ao servidor ACBr.');
    }

    public function cancelar(string $chave, string $justificativa): array
    {
        throw new RuntimeException('Cancelamento real do ACBr ainda nao esta disponivel.');
    }

    public function statusServico(): array
    {
        return [
            'status' => 'degraded',
            'driver' => 'real',
            'sefaz' => 'unknown',
        ];
    }

    public function consultar(string $chave): array
    {
        throw new RuntimeException('Consulta real do ACBr ainda nao esta disponivel.');
    }

    public function cartaCorrecao(string $chave, string $correcao): array
    {
        throw new RuntimeException('CC-e real do ACBr ainda nao esta disponivel.');
    }

    public function inutilizar(array $payload): array
    {
        throw new RuntimeException('Inutilizacao real do ACBr ainda nao esta disponivel.');
    }

    public function certificadoStatus(): array
    {
        return [
            'status' => 'unknown',
            'dias_restantes' => null,
        ];
    }
}
