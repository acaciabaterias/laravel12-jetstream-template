<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\PaymentReturnProcessingStatus;
use Database\Factories\RetornoPagamentoSaaSFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetornoPagamentoSaaS extends Model
{
    /** @use HasFactory<RetornoPagamentoSaaSFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'retornos_pagamento_saas';

    protected $fillable = [
        'gateway_cobranca_saas_id',
        'cobranca_saas_externa_id',
        'source_type',
        'external_event_id',
        'external_reference',
        'event_type',
        'payload',
        'received_at',
        'processed_at',
        'processing_status',
        'processing_error',
        'idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'processing_status' => PaymentReturnProcessingStatus::class,
            'metadata' => 'array',
        ];
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(GatewayCobrancaSaaS::class, 'gateway_cobranca_saas_id');
    }

    public function cobranca(): BelongsTo
    {
        return $this->belongsTo(CobrancaSaaSExterna::class, 'cobranca_saas_externa_id');
    }
}
