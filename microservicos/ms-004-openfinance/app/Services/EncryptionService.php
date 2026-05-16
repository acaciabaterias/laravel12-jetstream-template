<?php

namespace App\Services;

use Exception;

class EncryptionService
{
    protected string $key;

    protected string $cipher = 'aes-256-gcm';

    public function __construct()
    {
        $this->key = (string) config('services.openfinance.token_key');

        if (strlen($this->key) !== 32) {
            throw new Exception('OPENFINANCE_TOKEN_KEY deve ter exatamente 32 caracteres.');
        }
    }

    public function encrypt(string $data): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv, $tag);

        return base64_encode($iv.$tag.$encrypted);
    }

    public function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLen);
        $tag = substr($data, $ivLen, 16);
        $encrypted = substr($data, $ivLen + 16);

        $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decrypted === false) {
            throw new Exception('Falha na decriptacao do token bancario.');
        }

        return $decrypted;
    }
}
