<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ContaBancaria;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class ContaBancariaPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function view(User $user, ContaBancaria $contaBancaria): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function update(User $user, ContaBancaria $contaBancaria): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function delete(User $user, ContaBancaria $contaBancaria): bool
    {
        return $this->can($user, ['dono']) && $contaBancaria->status !== 'ativa';
    }
}
