<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CnabRetornoUpload;
use App\Models\User;
use App\Policies\Concerns\HandlesErpAuthorization;

class CnabRetornoUploadPolicy
{
    use HandlesErpAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function view(User $user, CnabRetornoUpload $cnabRetornoUpload): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function update(User $user, CnabRetornoUpload $cnabRetornoUpload): bool
    {
        return $this->can($user, ['dono', 'gestor']);
    }

    public function delete(User $user, CnabRetornoUpload $cnabRetornoUpload): bool
    {
        return false;
    }
}
