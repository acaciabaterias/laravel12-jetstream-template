<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformCurrencyInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string', Rule::in(['active', 'superseded', 'rolled_back'])],
            'severity' => ['nullable', 'string', Rule::in(['warning', 'critical'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'publication_limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
