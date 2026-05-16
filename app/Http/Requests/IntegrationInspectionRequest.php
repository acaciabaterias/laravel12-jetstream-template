<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:pending,processing,processed,failed,dead_letter,replayed,skipped'],
            'direction' => ['nullable', 'string', 'in:inbound,outbound'],
            'target' => ['nullable', 'string', 'max:180'],
            'tenant_external_ref' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
