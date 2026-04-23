<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EstoqueMovimentacao;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class EstoqueMovimentacaoPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'estoquista']);
    }

    public function view(User $user, EstoqueMovimentacao $estoqueMovimentacao): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor', 'estoquista']);
    }

    public function update(User $user, EstoqueMovimentacao $estoqueMovimentacao): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function delete(User $user, EstoqueMovimentacao $estoqueMovimentacao): bool
    {
        return false;
    }
}
