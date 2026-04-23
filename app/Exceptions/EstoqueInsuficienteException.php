<?php

declare(strict_types=1);

namespace App\Exceptions;

class EstoqueInsuficienteException extends BusinessRuleException
{
    public function __construct(
        public readonly ?int $bateriaId = null,
        public readonly ?int $quantidadeSolicitada = null,
        string $message = 'Estoque insuficiente para concluir a operação.',
    ) {
        parent::__construct($message, 422);
    }
}
