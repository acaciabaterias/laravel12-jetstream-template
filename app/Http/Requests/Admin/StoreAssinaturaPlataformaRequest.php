<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssinaturaPlataformaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'billing']);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', Rule::exists(Cliente::class, 'id')],
            'plano_id' => ['required', 'integer', Rule::exists(PlanoComercial::class, 'id')],
            'politica_inadimplencia_id' => ['nullable', 'integer', Rule::exists(PoliticaInadimplencia::class, 'id')],
            'status' => ['required', Rule::in(['trial', 'active'])],
            'data_inicio' => ['nullable', 'date'],
            'data_proximo_ciclo' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Selecione o assinante.',
            'plano_id.required' => 'Selecione o plano comercial.',
            'status.required' => 'Selecione o status inicial da assinatura.',
        ];
    }
}
