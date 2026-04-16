<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanoAssinatura extends Model
{
    use HasFactory;
    protected $table = 'planos_assinatura';

    protected $fillable = [
        'nome', 'slug', 'preco_mensal', 'preco_anual', 'max_usuarios',
        'max_estoque_itens', 'has_white_label', 'has_api_integration',
        'has_support_priority', 'stripe_price_id_mensal', 'stripe_price_id_anual',
        'ativo'
    ];

    protected function casts(): array
    {
        return [
            'preco_mensal' => 'decimal:2',
            'preco_anual' => 'decimal:2',
            'max_usuarios' => 'integer',
            'max_estoque_itens' => 'integer',
            'has_white_label' => 'boolean',
            'has_api_integration' => 'boolean',
            'has_support_priority' => 'boolean',
            'ativo' => 'boolean',
        ];
    }
}
