<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parada extends Model
{
    protected $table = 'paradas';

    protected $fillable = [
        'rota_id',
        'ordem',
        'entrega_id',
        'cliente_nome',
        'endereco',
        'latitude',
        'longitude',
        'eta_chegada',
        'status',
        'chegada_real',
        'saida_real',
    ];

    protected function casts(): array
    {
        return [
            'eta_chegada' => 'datetime',
            'chegada_real' => 'datetime',
            'saida_real' => 'datetime',
        ];
    }

    public function rota(): BelongsTo
    {
        return $this->belongsTo(Rota::class, 'rota_id');
    }
}
