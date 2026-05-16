<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookRecebido extends Model
{
    protected $table = 'webhook_recebidos';

    protected $fillable = [
        'banco_slug',
        'payload_raw',
        'evento',
        'processado',
    ];

    protected function casts(): array
    {
        return [
            'payload_raw' => 'array',
            'processado' => 'boolean',
        ];
    }
}
