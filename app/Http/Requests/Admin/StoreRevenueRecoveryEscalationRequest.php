<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\CasoRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRevenueRecoveryEscalationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'billing']);
    }

    public function rules(): array
    {
        return [
            'caso_recuperacao_receita_id' => ['required', 'integer', Rule::exists(CasoRecuperacaoReceita::class, 'id')],
            'owner_user_id' => ['nullable', 'integer', Rule::exists(UsuarioPlataforma::class, 'id')],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'caso_recuperacao_receita_id.required' => 'Selecione o caso de recuperação.',
        ];
    }
}
