<?php

namespace App\Services;

use App\Contracts\AcbrDriver;
use App\Services\Drivers\AcbrMockDriver;
use App\Services\Drivers\AcbrRealDriver;
use RuntimeException;

class AcbrService
{
    protected AcbrDriver $driver;

    public function __construct()
    {
        $this->driver = $this->resolveDriver();
    }

    protected function resolveDriver(): AcbrDriver
    {
        $driverName = config('acbr.driver', 'mock');

        return match ($driverName) {
            'mock' => app(AcbrMockDriver::class),
            'real' => app(AcbrRealDriver::class),
            default => throw new RuntimeException("Driver ACBr [$driverName] nao suportado."),
        };
    }

    public function __call(string $method, array $arguments): mixed
    {
        return $this->driver->{$method}(...$arguments);
    }
}
