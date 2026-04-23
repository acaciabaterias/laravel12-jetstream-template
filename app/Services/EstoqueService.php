<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ItemVale;
use App\Models\User;
use App\Models\Vale;

/**
 * Fachada de dominio para operacoes de reserva/estorno de estoque.
 */
class EstoqueService
{
    public function __construct(private readonly ReservaEstoqueService $reservaEstoqueService) {}

    public function reservar(Vale $vale, ItemVale $itemVale, ?User $user = null): void
    {
        $this->reservaEstoqueService->reservar($vale, $itemVale, $user);
    }

    public function estornar(Vale $vale): void
    {
        $this->reservaEstoqueService->estornarPorVale($vale);
    }
}
