<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContingenciaQueue extends Model
{
    protected $table = 'contingencia_queue';

    protected $fillable = [
        'nota_id',
        'motivo',
        'tentativas_realizadas',
        'ultima_tentativa',
        'proxima_tentativa',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'ultima_tentativa' => 'datetime',
            'proxima_tentativa' => 'datetime',
        ];
    }
}
