<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasRole(['dono', 'gestor'])) {
            return $user->is($model);
        }

        return $user->filial_id !== null && $user->filial_id === $model->filial_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['dono', 'gestor']);
    }

    public function update(User $user, User $model): bool
    {
        if (! $user->hasRole(['dono', 'gestor'])) {
            return false;
        }

        if ($user->filial_id === null || $model->filial_id === null) {
            return false;
        }

        return $user->filial_id === $model->filial_id;
    }

    public function delete(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }

    public function restore(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    public function toggleActive(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }
}
