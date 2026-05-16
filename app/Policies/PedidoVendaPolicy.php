<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PedidoVenda;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class PedidoVendaPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']);
    }

    public function view(User $user, PedidoVenda $pedidoVenda): bool
    {
        return $this->viewAny($user) || $this->can($user, ['tecnico']);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']);
    }

    public function update(User $user, PedidoVenda $pedidoVenda): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']) && $pedidoVenda->status !== 'cancelado';
    }

    public function delete(User $user, PedidoVenda $pedidoVenda): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $pedidoVenda->status !== 'faturado';
    }

    public function faturar(User $user, PedidoVenda $pedidoVenda): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']) && $pedidoVenda->status !== 'faturado';
    }
}
