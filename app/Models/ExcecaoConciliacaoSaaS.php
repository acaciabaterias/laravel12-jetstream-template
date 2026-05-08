<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\PaymentExceptionStatus;
use Database\Factories\ExcecaoConciliacaoSaaSFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcecaoConciliacaoSaaS extends Model
{
    /** @use HasFactory<ExcecaoConciliacaoSaaSFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'excecoes_conciliacao_saas';

    protected $fillable = [
        'fatura_saas_id',
        'cobranca_saas_externa_id',
        'retorno_pagamento_saas_id',
        'conciliacao_pagamento_saas_id',
        'status',
        'exception_type',
        'severity',
        'impact_on_subscription',
        'opened_at',
        'resolved_at',
        'owner_user_id',
        'resolution_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentExceptionStatus::class,
            'opened_at' => 'datetime',
            'resolved_at' => 'datetime',
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

    public function conciliacao(): BelongsTo
    {
        return $this->belongsTo(ConciliacaoPagamentoSaaS::class, 'conciliacao_pagamento_saas_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'owner_user_id');
    }
}
