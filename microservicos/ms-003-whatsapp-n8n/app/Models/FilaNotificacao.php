<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilaNotificacao extends Model
{
    protected $table = 'fila_notificacaos';

    protected $fillable = [
        'evento',
        'destinatario',
        'canal',
        'payload',
        'status',
        'agendado_para',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'agendado_para' => 'datetime',
        ];
    }
}
