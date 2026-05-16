<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ReservaEstoque;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class ReservaEstoquePolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor', 'estoquista']);
    }

    public function view(User $user, ReservaEstoque $reservaEstoque): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor', 'estoquista']);
    }

    public function update(User $user, ReservaEstoque $reservaEstoque): bool
    {
        return $this->can($user, ['dono', 'gestor', 'estoquista']) && $reservaEstoque->status === 'reservada';
    }

    public function delete(User $user, ReservaEstoque $reservaEstoque): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $reservaEstoque->status !== 'confirmada';
    }
}
