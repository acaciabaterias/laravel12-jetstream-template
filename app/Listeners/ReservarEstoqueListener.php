<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ValeCriado;
use App\Services\EstoqueService;

/**
 * Reserva os itens do vale assim que o evento e disparado.
 */
class ReservarEstoqueListener
{
    public function __construct(private readonly EstoqueService $estoqueService) {}

    public function handle(ValeCriado $event): void
    {
        foreach ($event->vale->itens as $item) {
            $this->estoqueService->reservar($event->vale, $item, $event->vale->createdBy);
        }
    }
}
