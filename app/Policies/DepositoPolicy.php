<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deposito;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class DepositoPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'estoquista']);
    }

    public function view(User $user, Deposito $deposito): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'estoquista']);
    }

    public function update(User $user, Deposito $deposito): bool
    {
        return $this->can($user, ['dono', 'gestor', 'estoquista']);
    }

    public function delete(User $user, Deposito $deposito): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $deposito->status !== 'ativo';
    }
}
