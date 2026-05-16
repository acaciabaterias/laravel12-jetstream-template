<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncEvento extends Model
{
    use Auditable, HasFactory;

    protected $table = 'sync_eventos';

    protected $fillable = [
        'dispositivo_uuid',
        'entidade_tipo',
        'entidade_id',
        'payload_hash',
        'payload',
        'status',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'json',
            'processed_at' => 'datetime',
        ];
    }
}
