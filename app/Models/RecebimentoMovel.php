<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecebimentoMovel extends Model
{
    use Auditable, HasFactory;

    protected $table = 'recebimentos_moveis';

    protected $fillable = [
        'ponto_entrega_id',
        'valor',
        'metodo_pagamento',
        'status_sincronizado',
        'comprovante_path',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'status_sincronizado' => 'boolean',
        ];
    }

    public function pontoEntrega(): BelongsTo
    {
        return $this->belongsTo(PontoEntrega::class, 'ponto_entrega_id');
    }
}
