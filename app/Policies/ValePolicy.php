<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vale;
use App\Policies\Concerns\HandlesErpAuthorization;

class ValePolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor', 'tecnico', 'estoquista']);
    }

    public function view(User $user, Vale $vale): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']);
    }

    public function update(User $user, Vale $vale): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']) && $vale->status === 'aberto';
    }

    public function delete(User $user, Vale $vale): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $vale->status === 'aberto';
    }

    public function cancel(User $user, Vale $vale): bool
    {
        return $this->delete($user, $vale);
    }

    public function convert(User $user, Vale $vale): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']) && $vale->status === 'aberto';
    }
}
