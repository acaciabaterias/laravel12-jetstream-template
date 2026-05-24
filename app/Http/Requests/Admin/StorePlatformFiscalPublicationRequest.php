<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformFiscalPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supportedDirections = (array) config('platform_fiscal_rules.supported_directions', []);

        return [
            'catalogEntries' => ['required', 'array', 'min:1'],
            'catalogEntries.*.cfop_code' => ['required', 'string', 'size:4'],
            'catalogEntries.*.description' => ['required', 'string', 'max:255'],
            'catalogEntries.*.operation_direction' => ['required', 'string', Rule::in($supportedDirections)],
            'scenarioMappings' => ['required', 'array', 'min:1'],
            'scenarioMappings.*.scenario_key' => ['required', 'string', 'max:80'],
            'scenarioMappings.*.cfop_code' => ['required', 'string', 'size:4'],
            'scenarioMappings.*.classification_code' => ['nullable', 'string', 'max:40'],
            'scenarioMappings.*.operation_direction' => ['required', 'string', Rule::in($supportedDirections)],
            'scenarioMappings.*.validation_flags' => ['required', 'array', 'min:1'],
            'scenarioMappings.*.tax_profile' => ['required', 'array'],
            'scenarioMappings.*.tax_profile.ncm_code' => ['nullable', 'string', 'max:10'],
            'scenarioMappings.*.tax_profile.tax_regime' => ['required', 'string'],
            'scenarioMappings.*.tax_profile.cst_code' => ['nullable', 'string', 'max:4'],
            'scenarioMappings.*.tax_profile.csosn_code' => ['nullable', 'string', 'max:4'],
            'scenarioMappings.*.tax_profile.partner_type' => ['nullable', 'string', 'max:40'],
            'scenarioMappings.*.tax_profile.operation_purpose' => ['nullable', 'string', 'max:40'],
            'scenarioMappings.*.tax_profile.origin_state' => ['nullable', 'string', 'size:2'],
            'scenarioMappings.*.tax_profile.destination_state' => ['nullable', 'string', 'size:2'],
            'scenarioMappings.*.tax_profile.interstate_tax_rate' => ['nullable', 'numeric', 'between:0,100'],
            'scenarioMappings.*.tax_profile.tax_payload' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'catalogEntries.required' => 'Informe o catalogo de CFOPs da publicacao.',
            'scenarioMappings.required' => 'Informe os mapeamentos de cenarios fiscais.',
        ];
    }
}
