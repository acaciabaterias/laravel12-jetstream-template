<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplayIntegrationEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_id' => ['required', 'integer', 'exists:tenant.entregas_integracao,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
