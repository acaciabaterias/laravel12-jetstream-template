<?php

namespace App\Http\Requests\Admin;

use App\Models\Filial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFilialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        /** @var Filial|null $filial */
        $filial = $this->route('filial');

        return [
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('filiais', 'cnpj')->ignore($filial?->id),
            ],
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
