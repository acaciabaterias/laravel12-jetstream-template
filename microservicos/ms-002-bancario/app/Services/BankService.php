<?php

namespace App\Services;

use App\Contracts\BankDriver;
use App\Services\Drivers\BoletoPackageDriver;
use App\Services\Drivers\MockBankDriver;
use RuntimeException;

class BankService
{
    protected function resolveDriver(): BankDriver
    {
        $driverName = config('banking.driver', 'mock');

        return match ($driverName) {
            'mock' => app(MockBankDriver::class),
            'package' => app(BoletoPackageDriver::class),
            default => throw new RuntimeException("Driver bancario [{$driverName}] nao suportado."),
        };
    }

    public function __call(string $method, array $arguments): mixed
    {
        return $this->resolveDriver()->{$method}(...$arguments);
    }
}
