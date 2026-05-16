<?php

namespace App\Policies;

use App\Models\EntregaIntegracao;
use App\Models\User;

class IntegrationBackbonePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
    }

    public function view(User $user, EntregaIntegracao $entregaIntegracao): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
    }

    public function update(User $user, EntregaIntegracao $entregaIntegracao): bool
    {
        return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
    }

    public function delete(User $user, EntregaIntegracao $entregaIntegracao): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, EntregaIntegracao $entregaIntegracao): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, EntregaIntegracao $entregaIntegracao): bool
    {
        return $user->isSuperAdmin();
    }

    public function replay(User $user, EntregaIntegracao $entregaIntegracao): bool
    {
        return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
    }
}
