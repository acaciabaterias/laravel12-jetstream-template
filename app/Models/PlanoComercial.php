<?php

namespace App\Models;

use Database\Factories\PlanoComercialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanoComercial extends Model
{
    /** @use HasFactory<PlanoComercialFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'slug',
        'preco_mensal',
        'preco_anual',
        'periodicidade',
        'max_usuarios',
        'max_estoque_itens',
        'has_white_label',
        'has_support_priority',
        'ativo',
        'recursos',
        'beneficios',
    ];

    protected function casts(): array
    {
        return [
            'preco_mensal' => 'decimal:2',
            'preco_anual' => 'decimal:2',
            'max_usuarios' => 'integer',
            'max_estoque_itens' => 'integer',
            'has_white_label' => 'boolean',
            'has_support_priority' => 'boolean',
            'ativo' => 'boolean',
            'recursos' => 'array',
            'beneficios' => 'array',
        ];
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(AssinaturaPlataforma::class, 'plano_id');
    }
}
