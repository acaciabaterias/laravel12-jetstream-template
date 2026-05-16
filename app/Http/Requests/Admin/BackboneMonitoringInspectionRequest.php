<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BackboneMonitoringInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flow_name' => ['nullable', 'string', 'max:80'],
            'severity' => ['nullable', 'string', 'max:20'],
            'alert_status' => ['nullable', 'string', 'max:20'],
            'environment' => ['nullable', 'string', 'max:40'],
            'provisioning_status' => ['nullable', 'string', 'max:20'],
            'evidence_type' => ['nullable', 'string', 'max:60'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
