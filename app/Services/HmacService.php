<?php

namespace App\Services;

class HmacService
{
    /**
     * Gera uma assinatura HMAC-SHA256 para o payload.
     *
     * @param  string  $payload  Conteúdo da requisição (JSON, etc)
     * @param  string  $secret  Chave secreta compartilhada
     */
    public function generateSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verifica se a assinatura fornecida é válida para o payload.
     *
     * @param  string  $payload  Conteúdo da requisição
     * @param  string  $signature  Assinatura recebida no header
     * @param  string  $secret  Chave secreta compartilhada
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = $this->generateSignature($payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
