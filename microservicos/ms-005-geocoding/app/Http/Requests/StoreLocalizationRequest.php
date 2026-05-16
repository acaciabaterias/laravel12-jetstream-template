<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocalizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entregador_id' => ['required', 'integer'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'velocidade_kmh' => ['nullable', 'numeric'],
            'heading' => ['nullable', 'integer'],
            'timestamp' => ['nullable', 'date'],
        ];
    }
}
