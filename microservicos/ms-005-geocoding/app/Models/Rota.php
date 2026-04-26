<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rota extends Model
{
    protected $table = 'rotas';

    protected $fillable = [
        'tenant_id_externo',
        'base_operacional_id',
        'data_entrega',
        'status',
        'paradas_json',
        'distancia_total_km',
        'duracao_estimada_min',
        'otimizada_em',
    ];

    protected function casts(): array
    {
        return [
            'paradas_json' => 'array',
            'data_entrega' => 'date',
            'otimizada_em' => 'datetime',
            'distancia_total_km' => 'decimal:2',
        ];
    }

    public function paradas(): HasMany
    {
        return $this->hasMany(Parada::class)->orderBy('ordem');
    }
}
