<?php

namespace App\Contracts;

interface BankProviderInterface
{
    public function getAuthUrl(string $state): string;

    public function exchangeToken(string $code): array;

    public function fetchTransactions(string $accessToken, array $period = []): array;

    public function refreshToken(string $refreshToken): array;

    public function revoke(string $accessToken): bool;
}
