<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductionObservabilityInspectionRequest extends FormRequest
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
            'status' => ['nullable', 'string', 'max:20'],
            'incident_status' => ['nullable', 'string', 'max:20'],
            'scenario_name' => ['nullable', 'string', 'max:120'],
            'throughput_per_minute' => ['nullable', 'integer', 'min:1'],
            'p95_latency_ms' => ['nullable', 'integer', 'min:1'],
            'error_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
