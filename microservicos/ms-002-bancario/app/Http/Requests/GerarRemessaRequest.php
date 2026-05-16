<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GerarRemessaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banco_id' => ['required', 'exists:banco_perfils,id'],
            'cobranca_ids' => ['required', 'array', 'min:1'],
            'cobranca_ids.*' => ['required', 'string', 'exists:cobrancas,id'],
        ];
    }
}
