<?php

namespace App\Contracts;

interface BankDriver
{
    public function gerarBoleto(array $data): array;

    public function gerarPix(array $data): array;

    public function processarRetorno(string $base64): array;

    public function gerarRemessa(array $cobrancas): array;

    public function consultar(array $cobranca): array;

    public function cancelar(array $cobranca): array;

    public function validarWebhook(string $banco, array $payload, ?string $signature = null): bool;

    public function health(): array;
}
