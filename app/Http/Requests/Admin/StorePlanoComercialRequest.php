<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\PlanoComercial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanoComercialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'billing']);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique(PlanoComercial::class, 'slug')],
            'preco_mensal' => ['required', 'numeric', 'min:0'],
            'preco_anual' => ['nullable', 'numeric', 'min:0'],
            'periodicidade' => ['required', Rule::in(['mensal', 'trimestral', 'anual', 'custom'])],
            'max_usuarios' => ['required', 'integer', 'min:1'],
            'max_estoque_itens' => ['required', 'integer', 'min:1'],
            'has_white_label' => ['required', 'boolean'],
            'has_support_priority' => ['required', 'boolean'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome comercial do plano.',
            'slug.unique' => 'Já existe um plano com este identificador.',
            'preco_mensal.required' => 'Informe o valor mensal do plano.',
            'periodicidade.required' => 'Informe a periodicidade do plano.',
        ];
    }
}
