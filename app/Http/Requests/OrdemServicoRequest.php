<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Valida payloads de ordem de servico/garantia.
 */
class OrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['dono', 'gestor', 'tecnico', 'super_admin']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'laudo.texto' => ['required', 'string', 'min:10'],
            'laudo.fotos' => ['nullable', 'array'],
            'laudo.fotos.*' => ['string', 'regex:/^data:image\/[a-zA-Z0-9.+-]+;base64,/'],
            'resultado' => ['required', 'string', 'in:procedente,improcedente'],
            'bateria_emprestimo_id' => ['nullable', 'integer', 'exists:baterias_emprestimo,id'],
            'valor_recarga' => ['nullable', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $resultado = (string) $this->input('resultado');

            if ($resultado === 'procedente' && ! $this->filled('bateria_emprestimo_id')) {
                $validator->errors()->add('bateria_emprestimo_id', 'Selecione a bateria de emprestimo quando o resultado for procedente.');
            }

            if ($resultado === 'improcedente' && ! $this->filled('valor_recarga')) {
                $validator->errors()->add('valor_recarga', 'Informe o valor de recarga quando o resultado for improcedente.');
            }
        });
    }
}
