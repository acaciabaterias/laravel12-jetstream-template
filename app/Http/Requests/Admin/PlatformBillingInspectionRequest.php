<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformBillingInspectionRequest extends FormRequest
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
            'status' => ['nullable', Rule::in(['active', 'trial', 'grace_period', 'blocked', 'cancelled', 'expired'])],
            'plan' => ['nullable', 'string', 'max:80'],
            'risk' => ['nullable', Rule::in(['overdue', 'grace', 'blocked', 'reactivated'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
