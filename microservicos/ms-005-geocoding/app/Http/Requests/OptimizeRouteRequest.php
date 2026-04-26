<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptimizeRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id_externo' => ['required', 'string'],
            'base_operacional_id' => ['required', 'string'],
            'base_lat' => ['required', 'numeric'],
            'base_lng' => ['required', 'numeric'],
            'data_entrega' => ['required', 'date'],
            'entregas' => ['required', 'array', 'min:1'],
            'entregas.*.id' => ['required', 'integer'],
            'entregas.*.endereco' => ['required', 'string'],
            'entregas.*.cliente' => ['required', 'string'],
        ];
    }
}
