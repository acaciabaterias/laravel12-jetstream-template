<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoAssinatura extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'planos';

    protected $fillable = [
        'nome', 'slug', 'preco_mensal', 'max_usuarios',
        'max_estoque_itens', 'has_white_label',
        'has_support_priority', 'ativo',
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
