<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InutilizarFiscalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'serie' => ['required', 'integer', 'min:1'],
            'numero_inicial' => ['required', 'integer', 'min:1'],
            'numero_final' => ['required', 'integer', 'gte:numero_inicial'],
            'justificativa' => ['required', 'string', 'min:15', 'max:255'],
        ];
    }
}
