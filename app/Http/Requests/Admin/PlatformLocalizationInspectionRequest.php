<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformLocalizationInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['nullable', 'string', Rule::in(array_keys((array) config('platform_localization.supported_locales', [])))],
            'severity' => ['nullable', 'string', Rule::in(['warning', 'critical'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'publication_limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
