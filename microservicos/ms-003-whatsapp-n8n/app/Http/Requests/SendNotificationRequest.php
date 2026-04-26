<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to' => ['required', 'string', 'max:32'],
            'message' => ['required', 'string'],
            'evento' => ['required', 'string', 'max:100'],
            'canal' => ['nullable', 'string', 'in:whatsapp,email,sms'],
            'workflow_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
