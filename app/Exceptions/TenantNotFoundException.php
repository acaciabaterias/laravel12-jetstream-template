<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class TenantNotFoundException extends RuntimeException
{
    public function __construct(
        public readonly ?string $tenantIdentifier = null,
        string $message = 'Tenant não encontrado.',
    ) {
        parent::__construct($message);
    }
}
