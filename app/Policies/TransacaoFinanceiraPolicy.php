<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TransacaoFinanceira;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class TransacaoFinanceiraPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function view(User $user, TransacaoFinanceira $transacaoFinanceira): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function update(User $user, TransacaoFinanceira $transacaoFinanceira): bool
    {
        return $this->can($user, ['dono', 'gestor']) && ! $transacaoFinanceira->status_conciliado;
    }

    public function delete(User $user, TransacaoFinanceira $transacaoFinanceira): bool
    {
        return $this->can($user, ['dono']) && ! $transacaoFinanceira->status_conciliado;
    }

    public function conciliate(User $user, TransacaoFinanceira $transacaoFinanceira): bool
    {
        return $this->can($user, ['dono', 'gestor']) && ! $transacaoFinanceira->status_conciliado;
    }
}
