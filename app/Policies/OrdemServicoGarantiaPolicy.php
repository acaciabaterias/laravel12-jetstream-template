<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrdemServicoGarantia;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class OrdemServicoGarantiaPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'tecnico']);
    }

    public function view(User $user, OrdemServicoGarantia $ordemServicoGarantia): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'tecnico']);
    }

    public function update(User $user, OrdemServicoGarantia $ordemServicoGarantia): bool
    {
        return $this->can($user, ['dono', 'gestor', 'tecnico']) && $ordemServicoGarantia->status !== 'concluida';
    }

    public function delete(User $user, OrdemServicoGarantia $ordemServicoGarantia): bool
    {
        return $this->can($user, ['dono', 'gestor']) && $ordemServicoGarantia->status === 'aberta';
    }

    public function conclude(User $user, OrdemServicoGarantia $ordemServicoGarantia): bool
    {
        return $this->can($user, ['dono', 'gestor', 'tecnico']) && $ordemServicoGarantia->status !== 'concluida';
    }
}
