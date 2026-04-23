<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class UserPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function view(User $user, User $model): bool
    {
        return $this->viewAny($user) || $user->is($model);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function update(User $user, User $model): bool
    {
        return $this->can($user, ['dono', 'gestor']) || $user->is($model);
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
