<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NotaFiscalOrquestrada;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class NotaFiscalOrquestradaPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']);
    }

    public function view(User $user, NotaFiscalOrquestrada $notaFiscalOrquestrada): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']);
    }

    public function update(User $user, NotaFiscalOrquestrada $notaFiscalOrquestrada): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function delete(User $user, NotaFiscalOrquestrada $notaFiscalOrquestrada): bool
    {
        return false;
    }

    public function issue(User $user, NotaFiscalOrquestrada $notaFiscalOrquestrada): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor']) && $notaFiscalOrquestrada->status !== 'emitida';
    }
}
