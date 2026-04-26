<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaptureExtratoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periodo_de' => ['nullable', 'date'],
            'periodo_ate' => ['nullable', 'date'],
        ];
    }
}
