<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'nota_id',
        'acao',
        'payload_entrada',
        'payload_saida',
        'status_http',
    ];

    protected function casts(): array
    {
        return [
            'payload_entrada' => 'array',
            'payload_saida' => 'array',
        ];
    }
}
