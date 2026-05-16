<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExecutiveReportingInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', 'min:7', 'max:365'],
            'plan' => ['nullable', 'string', 'max:60'],
            'channel' => ['nullable', 'string', 'max:60'],
            'portfolio' => ['nullable', 'string', 'max:60'],
            'recovery_status' => ['nullable', 'string', 'max:60'],
            'format' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:20'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
