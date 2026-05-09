<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlatformCommercialAnalyticsInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'snapshot_id' => ['nullable', 'integer'],
            'metric_key' => ['nullable', 'string', 'max:60'],
            'dimension_type' => ['nullable', 'string', 'max:40'],
            'source_type' => ['nullable', 'string', 'max:60'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
