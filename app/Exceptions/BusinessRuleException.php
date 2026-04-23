<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class BusinessRuleException extends RuntimeException
{
    public function __construct(
        string $message = 'Uma regra de negócio foi violada.',
        public readonly int $status = 422,
    ) {
        parent::__construct($message, $status);
    }
}
