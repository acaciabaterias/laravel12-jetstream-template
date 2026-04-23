<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

trait HandlesErpAuthorization
{
    protected function can(User $user, array $roles): bool
    {
        return $user->isSuperAdmin() || ($user->ativo && $user->hasRole($roles));
    }
}
