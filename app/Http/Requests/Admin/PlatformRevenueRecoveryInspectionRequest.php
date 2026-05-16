<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformRevenueRecoveryInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'support', 'billing']);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['open', 'paused', 'escalated', 'recovered', 'closed', 'cancelled'])],
            'stage' => ['nullable', 'string', 'max:60'],
            'severity' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
            'owner' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
