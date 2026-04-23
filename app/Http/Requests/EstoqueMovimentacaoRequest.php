<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Valida movimentacoes de estoque.
 */
class EstoqueMovimentacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['dono', 'gestor', 'estoquista', 'super_admin']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bateria_id' => ['required', 'integer', 'exists:baterias,id'],
            'tipo' => ['required', 'string', 'in:entrada,saida,transferencia,ajuste'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'deposito_id' => ['nullable', 'integer', 'exists:depositos,id'],
            'deposito_origem_id' => ['nullable', 'integer', 'exists:depositos,id'],
            'deposito_destino_id' => ['nullable', 'integer', 'exists:depositos,id'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tipo = (string) $this->input('tipo');
            $depositoOrigemId = $this->integer('deposito_origem_id');
            $depositoDestinoId = $this->integer('deposito_destino_id');

            if ($tipo === 'transferencia' && $depositoOrigemId === $depositoDestinoId) {
                $validator->errors()->add('deposito_destino_id', 'O deposito de destino deve ser diferente do deposito de origem.');
            }

            if ($tipo === 'ajuste' && blank($this->input('motivo'))) {
                $validator->errors()->add('motivo', 'Informe o motivo do ajuste de estoque.');
            }
        });
    }
}
