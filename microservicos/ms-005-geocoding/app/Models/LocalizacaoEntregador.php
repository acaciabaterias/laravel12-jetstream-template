<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalizacaoEntregador extends Model
{
    protected $table = 'localizacao_entregadores';

    protected $fillable = [
        'entregador_id',
        'latitude',
        'longitude',
        'velocidade_kmh',
        'heading',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'velocidade_kmh' => 'decimal:2',
            'timestamp' => 'datetime',
        ];
    }
}
