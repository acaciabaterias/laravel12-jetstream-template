<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\UsuarioPlataforma;

class TenantPolicy
{
    public function viewAny(UsuarioPlataforma $user): bool
    {
        return $user->ativo;
    }

    public function view(UsuarioPlataforma $user, Cliente $tenant): bool
    {
        return $user->ativo;
    }

    public function create(UsuarioPlataforma $user): bool
    {
        return $user->ativo && $user->isSuperAdmin();
    }

    public function update(UsuarioPlataforma $user, Cliente $tenant): bool
    {
        return $user->ativo && $user->isSuperAdmin();
    }

    public function delete(UsuarioPlataforma $user, Cliente $tenant): bool
    {
        return $user->ativo && $user->isSuperAdmin();
    }

    public function toggleStatus(UsuarioPlataforma $user, Cliente $tenant): bool
    {
        return $user->ativo && $user->isSuperAdmin();
    }
}
