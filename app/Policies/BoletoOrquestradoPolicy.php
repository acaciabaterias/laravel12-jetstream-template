<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BoletoOrquestrado;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class BoletoOrquestradoPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function view(User $user, BoletoOrquestrado $boletoOrquestrado): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function update(User $user, BoletoOrquestrado $boletoOrquestrado): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $boletoOrquestrado->status !== 'liquidado';
    }

    public function delete(User $user, BoletoOrquestrado $boletoOrquestrado): bool
    {
        return false;
    }

    public function emit(User $user, BoletoOrquestrado $boletoOrquestrado): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $boletoOrquestrado->status !== 'emitido';
    }
}
