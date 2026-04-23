<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ValeCancelado;
use App\Services\EstoqueService;

/**
 * Estorna reservas quando um vale e cancelado.
 */
class EstornarEstoqueListener
{
    public function __construct(private readonly EstoqueService $estoqueService) {}

    public function handle(ValeCancelado $event): void
    {
        $this->estoqueService->estornar($event->vale);
    }
}
