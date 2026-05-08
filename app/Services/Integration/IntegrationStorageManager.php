<?php

declare(strict_types=1);

namespace App\Services\Integration;

class IntegrationStorageManager
{
    private static string $connectionName = 'tenant';

    public function currentConnection(): string
    {
        return self::$connectionName;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function using(string $connectionName, callable $callback): mixed
    {
        $previousConnection = self::$connectionName;
        self::$connectionName = $connectionName;

        try {
            return $callback();
        } finally {
            self::$connectionName = $previousConnection;
        }
    }
}
