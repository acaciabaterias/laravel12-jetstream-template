<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectGeocodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ];
    }
}
