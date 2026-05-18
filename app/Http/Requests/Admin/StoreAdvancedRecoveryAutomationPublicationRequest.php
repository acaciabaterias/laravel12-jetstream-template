<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvancedRecoveryAutomationPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'billing']);
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'scope_filters' => ['required', 'array'],
            'guardrail_rules' => ['required', 'array'],
            'guardrail_rules.max_dispatches_per_day' => ['required', 'integer', 'min:1'],
            'guardrail_rules.cooldown_hours' => ['required', 'integer', 'min:1'],
            'guardrail_rules.suppression_hours' => ['required', 'integer', 'min:1'],
            'fallback_matrix' => ['required', 'array'],
            'fallback_matrix.stage_channels' => ['required', 'array'],
            'fallback_matrix.fallbacks' => ['required', 'array', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Informe o slug da politica.',
            'name.required' => 'Informe o nome da politica.',
            'guardrail_rules.max_dispatches_per_day.required' => 'Defina a frequencia maxima por dia.',
            'fallback_matrix.stage_channels.required' => 'Defina ao menos um stage_channels.',
        ];
    }
}
