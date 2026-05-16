<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CnabRemessa;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class CnabRemessaPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function view(User $user, CnabRemessa $cnabRemessa): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function update(User $user, CnabRemessa $cnabRemessa): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $cnabRemessa->status !== 'processada';
    }

    public function delete(User $user, CnabRemessa $cnabRemessa): bool
    {
        return false;
    }

    public function process(User $user, CnabRemessa $cnabRemessa): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }
}
