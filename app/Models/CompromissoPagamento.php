<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\PaymentPromiseStatus;
use Database\Factories\CompromissoPagamentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompromissoPagamento extends Model
{
    /** @use HasFactory<CompromissoPagamentoFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'compromissos_pagamento';

    protected $fillable = [
        'caso_recuperacao_receita_id',
        'promised_amount',
        'promised_date',
        'status',
        'recorded_by_user_id',
        'notes',
        'suspends_until',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'promised_amount' => 'decimal:2',
            'promised_date' => 'date',
            'status' => PaymentPromiseStatus::class,
            'suspends_until' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoRecuperacaoReceita::class, 'caso_recuperacao_receita_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'recorded_by_user_id');
    }
}
