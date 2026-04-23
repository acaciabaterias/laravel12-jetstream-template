<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida a criacao e atualizacao de usuarios do tenant.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authenticatedUser = auth()->user();

        if (! $authenticatedUser) {
            return false;
        }

        if (! $authenticatedUser->hasRole(['dono', 'gestor', 'super_admin'])) {
            return false;
        }

        return ! ($this->input('papel') === 'dono' && ! $authenticatedUser->isSuperAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'papel' => ['required', 'string', 'in:dono,gestor,vendedor,tecnico,estoquista,entregador'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
