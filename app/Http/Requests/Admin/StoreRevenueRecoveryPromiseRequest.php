<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\CasoRecuperacaoReceita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRevenueRecoveryPromiseRequest extends FormRequest
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
            'promised_amount' => ['nullable', 'numeric', 'min:0'],
            'promised_date' => ['required', 'date'],
            'suspends_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'caso_recuperacao_receita_id.required' => 'Selecione o caso de recuperação.',
            'promised_date.required' => 'Informe a data prometida.',
        ];
    }
}
