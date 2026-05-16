<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida a criacao e atualizacao de vales.
 */
class ValeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['dono', 'gestor', 'vendedor', 'super_admin']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.id' => ['required', 'integer', 'exists:baterias,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.preco' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Selecione um cliente para o vale.',
            'itens.required' => 'Adicione pelo menos um item ao vale.',
            'itens.min' => 'Adicione pelo menos um item ao vale.',
            'itens.*.id.exists' => 'O item informado nao corresponde a uma bateria valida.',
            'itens.*.quantidade.min' => 'A quantidade de cada item deve ser maior que zero.',
            'itens.*.preco.min' => 'O preco de cada item deve ser maior que zero.',
        ];
    }
}
