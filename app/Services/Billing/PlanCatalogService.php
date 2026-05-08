<?php

namespace App\Services\Billing;

use App\Models\PlanoComercial;
use Illuminate\Support\Str;

class PlanCatalogService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PlanoComercial
    {
        return PlanoComercial::query()->create($this->normalizeAttributes($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(PlanoComercial $planoComercial, array $attributes): PlanoComercial
    {
        $planoComercial->update($this->normalizeAttributes($attributes, $planoComercial));

        return $planoComercial->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes, ?PlanoComercial $planoComercial = null): array
    {
        $nome = $attributes['nome'] ?? $planoComercial?->nome ?? 'Plano';

        return [
            'nome' => $nome,
            'slug' => $attributes['slug'] ?? $planoComercial?->slug ?? Str::slug($nome),
            'preco_mensal' => $attributes['preco_mensal'] ?? $planoComercial?->preco_mensal ?? 0,
            'preco_anual' => $attributes['preco_anual'] ?? $planoComercial?->preco_anual,
            'periodicidade' => $attributes['periodicidade'] ?? $planoComercial?->periodicidade ?? 'mensal',
            'max_usuarios' => $attributes['max_usuarios'] ?? $planoComercial?->max_usuarios ?? 3,
            'max_estoque_itens' => $attributes['max_estoque_itens'] ?? $planoComercial?->max_estoque_itens ?? 500,
            'has_white_label' => $attributes['has_white_label'] ?? $planoComercial?->has_white_label ?? false,
            'has_support_priority' => $attributes['has_support_priority'] ?? $planoComercial?->has_support_priority ?? false,
            'ativo' => $attributes['ativo'] ?? $planoComercial?->ativo ?? true,
            'recursos' => $attributes['recursos'] ?? $planoComercial?->recursos ?? [],
            'beneficios' => $attributes['beneficios'] ?? $planoComercial?->beneficios ?? [],
        ];
    }
}
