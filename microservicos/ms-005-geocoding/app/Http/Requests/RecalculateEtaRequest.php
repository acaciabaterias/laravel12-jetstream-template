<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecalculateEtaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rota_id' => ['required', 'exists:rotas,id'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ];
    }
}
