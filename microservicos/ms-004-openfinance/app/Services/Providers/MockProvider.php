<?php

namespace App\Services\Providers;

use App\Contracts\BankProviderInterface;
use Illuminate\Support\Str;

class MockProvider implements BankProviderInterface
{
    public function getAuthUrl(string $state): string
    {
        return 'http://localhost:8004/mock/oauth/authorize?client_id=mock&scope=accounts&state='.$state;
    }

    public function exchangeToken(string $code): array
    {
        return [
            'access_token' => 'MOCK_AT_'.Str::random(20),
            'refresh_token' => 'MOCK_RT_'.Str::random(20),
            'expires_in' => 3600,
        ];
    }

    public function fetchTransactions(string $accessToken, array $period = []): array
    {
        return [
            [
                'tx_id' => 'MOCK_TX_'.uniqid(),
                'data' => now()->format('Y-m-d'),
                'valor' => -50.25,
                'descricao' => 'MOCK TRANSACTION - COFFEE SHOP',
                'tipo' => 'debito',
            ],
            [
                'tx_id' => 'MOCK_TX_'.uniqid(),
                'data' => now()->subDay()->format('Y-m-d'),
                'valor' => 1500.00,
                'descricao' => 'MOCK TRANSACTION - DEPOSIT',
                'tipo' => 'credito',
            ],
        ];
    }

    public function refreshToken(string $refreshToken): array
    {
        return [
            'access_token' => 'MOCK_AT_NEW_'.Str::random(20),
            'refresh_token' => 'MOCK_RT_NEW_'.Str::random(20),
            'expires_in' => 3600,
        ];
    }

    public function revoke(string $accessToken): bool
    {
        return true;
    }
}
