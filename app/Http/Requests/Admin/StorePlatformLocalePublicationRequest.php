<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformLocalePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supportedLocales = array_keys((array) config('platform_localization.supported_locales', []));

        return [
            'selectedLocales' => ['required', 'array', 'min:1'],
            'selectedLocales.*' => ['required', 'string', Rule::in($supportedLocales)],
            'defaultLocale' => ['required', 'string', Rule::in($supportedLocales)],
            'fallbackLocale' => ['required', 'string', Rule::in($supportedLocales)],
        ];
    }

    public function messages(): array
    {
        return [
            'selectedLocales.required' => 'Selecione pelo menos um locale suportado.',
            'defaultLocale.required' => 'Informe o locale padrao.',
            'fallbackLocale.required' => 'Informe o locale de fallback.',
        ];
    }
}
