<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFilialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18', Rule::unique('filiais', 'cnpj')],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome da filial.',
            'cnpj.unique' => 'Já existe uma filial com este CNPJ.',
        ];
    }
}
