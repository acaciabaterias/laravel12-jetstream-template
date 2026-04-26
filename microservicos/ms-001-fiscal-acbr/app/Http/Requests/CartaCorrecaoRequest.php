<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartaCorrecaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chave_acesso' => ['required', 'string', 'size:44'],
            'correcao' => ['required', 'string', 'min:15', 'max:1000'],
        ];
    }
}
