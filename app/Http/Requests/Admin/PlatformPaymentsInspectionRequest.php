<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformPaymentsInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'support', 'billing']);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['draft', 'submitted', 'pending', 'paid', 'expired', 'cancelled', 'failed', 'refunded', 'chargeback'])],
            'channel' => ['nullable', Rule::in(['boleto', 'pix'])],
            'exception' => ['nullable', Rule::in(['amount_mismatch', 'reference_mismatch', 'duplicate_event', 'chargeback', 'refund', 'gateway_failure', 'unknown'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
