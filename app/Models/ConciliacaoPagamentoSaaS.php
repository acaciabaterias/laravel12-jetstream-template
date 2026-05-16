<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\PaymentReconciliationStatus;
use Database\Factories\ConciliacaoPagamentoSaaSFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConciliacaoPagamentoSaaS extends Model
{
    /** @use HasFactory<ConciliacaoPagamentoSaaSFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'conciliacoes_pagamento_saas';

    protected $fillable = [
        'fatura_saas_id',
        'cobranca_saas_externa_id',
        'retorno_pagamento_saas_id',
        'status',
        'reconciliation_type',
        'expected_amount',
        'received_amount',
        'difference_amount',
        'reconciled_at',
        'operator_user_id',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentReconciliationStatus::class,
            'expected_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'reconciled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function fatura(): BelongsTo
    {
        return $this->belongsTo(FaturaSaaS::class, 'fatura_saas_id');
    }

    public function cobranca(): BelongsTo
    {
        return $this->belongsTo(CobrancaSaaSExterna::class, 'cobranca_saas_externa_id');
    }

    public function retorno(): BelongsTo
    {
        return $this->belongsTo(RetornoPagamentoSaaS::class, 'retorno_pagamento_saas_id');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_user_id');
    }
}
