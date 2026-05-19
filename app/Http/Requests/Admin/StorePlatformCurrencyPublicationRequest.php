<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformCurrencyPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supportedCurrencyCodes = array_keys((array) config('platform_currencies.supported_currencies', []));

        return [
            'selectedCurrencies' => ['required', 'array', 'min:1'],
            'selectedCurrencies.*' => ['required', 'string', Rule::in($supportedCurrencyCodes)],
            'baseCurrency' => ['required', 'string', Rule::in($supportedCurrencyCodes)],
            'defaultCurrency' => ['required', 'string', Rule::in($supportedCurrencyCodes)],
            'exchangeRates' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'selectedCurrencies.required' => 'Selecione ao menos uma moeda suportada.',
            'baseCurrency.required' => 'Informe a moeda base.',
            'defaultCurrency.required' => 'Informe a moeda padrao.',
            'exchangeRates.required' => 'Informe a tabela de cambio da publicacao.',
        ];
    }
}
