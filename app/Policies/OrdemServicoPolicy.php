<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrdemServico;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class OrdemServicoPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor', 'tecnico']);
    }

    public function view(User $user, OrdemServico $ordemServico): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'vendedor', 'tecnico']);
    }

    public function update(User $user, OrdemServico $ordemServico): bool
    {
        return $this->can($user, ['dono', 'gestor', 'tecnico']) && $ordemServico->status !== 'concluida';
    }

    public function delete(User $user, OrdemServico $ordemServico): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $ordemServico->status === 'aberta';
    }

    public function close(User $user, OrdemServico $ordemServico): bool
    {
        return $this->can($user, ['dono', 'gestor', 'tecnico']) && $ordemServico->status !== 'concluida';
    }
}
