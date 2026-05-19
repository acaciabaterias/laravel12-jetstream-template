<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformCurrencyPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userCurrency' => [
                'required',
                'string',
                Rule::in(array_keys((array) config('platform_currencies.supported_currencies', []))),
            ],
        ];
    }
}
