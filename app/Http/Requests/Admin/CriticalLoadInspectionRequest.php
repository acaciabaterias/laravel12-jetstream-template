<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CriticalLoadInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flow_name' => ['nullable', 'string', 'max:80'],
            'comparison_status' => ['nullable', 'string', 'max:20'],
            'category' => ['nullable', 'string', 'max:30'],
            'environment' => ['nullable', 'string', 'max:40'],
            'tuning_status' => ['nullable', 'string', 'max:20'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
