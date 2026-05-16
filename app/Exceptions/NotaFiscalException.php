<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class NotaFiscalException extends RuntimeException
{
    public function __construct(
        string $message = 'Não foi possível emitir a nota fiscal.',
        public readonly int $status = 502,
    ) {
        parent::__construct($message, $status);
    }
}
