<?php

namespace App\Contracts;

interface AcbrDriver
{
    public function emitir(array $data): array;

    public function cancelar(string $chave, string $justificativa): array;

    public function statusServico(): array;

    public function consultar(string $chave): array;

    public function cartaCorrecao(string $chave, string $correcao): array;

    public function inutilizar(array $payload): array;

    public function certificadoStatus(): array;
}
