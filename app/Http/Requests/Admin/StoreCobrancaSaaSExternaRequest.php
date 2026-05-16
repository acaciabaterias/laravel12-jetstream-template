<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCobrancaSaaSExternaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('platform')->check()
            && auth('platform')->user()->hasRole(['super_admin', 'billing']);
    }

    public function rules(): array
    {
        return [
            'fatura_saas_id' => ['required', 'integer', Rule::exists(FaturaSaaS::class, 'id')],
            'gateway_cobranca_saas_id' => ['required', 'integer', Rule::exists(GatewayCobrancaSaaS::class, 'id')],
            'payment_channel' => ['required', Rule::in(['boleto', 'pix'])],
            'force_reissue' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fatura_saas_id.required' => 'Selecione a fatura SaaS.',
            'gateway_cobranca_saas_id.required' => 'Selecione o gateway de cobrança.',
            'payment_channel.required' => 'Selecione o meio de pagamento.',
            'payment_channel.in' => 'O meio de pagamento informado não é suportado.',
        ];
    }
}
