<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilaContingencia extends Model
{
    use Auditable, HasFactory;

    protected $table = 'filas_contingencia';

    protected $fillable = [
        'tipo_integracao',
        'payload',
        'tentativas',
        'proxima_tentativa',
        'status',
        'ultimo_erro',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'json',
            'proxima_tentativa' => 'datetime',
        ];
    }
}
