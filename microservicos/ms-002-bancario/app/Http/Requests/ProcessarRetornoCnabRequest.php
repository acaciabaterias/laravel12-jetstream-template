<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessarRetornoCnabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banco_id' => ['required', 'exists:banco_perfils,id'],
            'arquivo_nome' => ['nullable', 'string', 'max:255'],
            'arquivo_base64' => ['required', 'string'],
        ];
    }
}
