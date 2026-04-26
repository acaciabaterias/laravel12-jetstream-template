<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmitirBoletoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'string', 'max:255'],
            'erp_fatura_id' => ['required', 'integer', 'min:1'],
            'banco_id' => ['required', 'exists:banco_perfils,id'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'vencimento' => ['required', 'date'],
            'sacado' => ['required', 'array'],
            'sacado.nome' => ['required', 'string', 'max:255'],
            'sacado.documento' => ['required', 'string', 'max:32'],
        ];
    }
}
