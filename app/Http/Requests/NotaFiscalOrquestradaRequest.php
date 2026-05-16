<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Valida o disparo de NF-e/NFC-e na camada de orquestracao.
 */
class NotaFiscalOrquestradaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['dono', 'gestor', 'vendedor', 'super_admin']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vale_id' => ['required', 'integer', 'exists:vales,id'],
            'tipo' => ['required', 'string', 'in:nfe,nfce'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('tipo') !== 'nfe') {
                return;
            }

            $vale = Vale::query()->with('cliente')->find($this->integer('vale_id'));
            $cnpj = preg_replace('/\D+/', '', (string) $vale?->cliente?->cnpj);

            if ($cnpj === '') {
                $validator->errors()->add('vale_id', 'Para NF-e, o cliente do vale deve possuir CNPJ informado.');
            }
        });
    }
}
