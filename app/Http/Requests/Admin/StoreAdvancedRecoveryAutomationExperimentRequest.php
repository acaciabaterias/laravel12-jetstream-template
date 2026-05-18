<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvancedRecoveryAutomationExperimentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'billing']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'control_ratio' => ['required', 'numeric', 'min:0', 'max:1'],
            'allocation_rules' => ['nullable', 'array'],
            'variant_definitions' => ['required', 'array', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do experimento.',
            'control_ratio.required' => 'Informe o percentual de holdout.',
            'variant_definitions.required' => 'Defina ao menos uma variante ativa.',
        ];
    }
}
