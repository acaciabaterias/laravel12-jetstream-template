<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class MicroservicoIndisponivelException extends RuntimeException
{
    public function __construct(
        public readonly string $service,
        string $message = 'Microserviço indisponível no momento.',
        public readonly int $status = 503,
    ) {
        parent::__construct($message, $status);
    }
}
