<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformFiscalRuleInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scenario' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'active', 'superseded', 'rolled_back'])],
            'severity' => ['nullable', 'string', Rule::in(['warning', 'critical'])],
            'issue_type' => ['nullable', 'string', 'max:80'],
            'origin_state' => ['nullable', 'string', 'size:2'],
            'destination_state' => ['nullable', 'string', 'size:2'],
            'partner_type' => ['nullable', 'string', 'max:40'],
            'operation_purpose' => ['nullable', 'string', 'max:40'],
            'tax_regime' => ['nullable', 'string', 'max:40'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'publication_limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
