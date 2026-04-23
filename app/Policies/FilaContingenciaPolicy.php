<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FilaContingencia;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class FilaContingenciaPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function view(User $user, FilaContingencia $filaContingencia): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function update(User $user, FilaContingencia $filaContingencia): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function delete(User $user, FilaContingencia $filaContingencia): bool
    {
        return false;
    }

    public function retry(User $user, FilaContingencia $filaContingencia): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }
}
