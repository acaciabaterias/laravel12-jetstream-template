<?php

namespace App\Policies;

use App\Models\UsuarioPlataforma;

class PlatformPaymentsPolicy
{
    public function viewAny(UsuarioPlataforma $user): bool
    {
        return $user->ativo && $user->hasRole(['super_admin', 'billing']);
    }

    public function view(UsuarioPlataforma $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(UsuarioPlataforma $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(UsuarioPlataforma $user): bool
    {
        return $this->viewAny($user);
    }

    public function delete(UsuarioPlataforma $user): bool
    {
        return $user->ativo && $user->isSuperAdmin();
    }
}
