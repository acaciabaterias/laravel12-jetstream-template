<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebhookBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'txid' => ['nullable', 'string', 'max:255'],
            'nosso_numero' => ['nullable', 'string', 'max:255'],
            'valor_pago' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'evento' => ['nullable', 'string', 'max:100'],
        ];
    }
}
