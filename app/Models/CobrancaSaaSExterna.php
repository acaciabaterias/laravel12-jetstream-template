<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\ExternalChargeStatus;
use Database\Factories\CobrancaSaaSExternaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CobrancaSaaSExterna extends Model
{
    /** @use HasFactory<CobrancaSaaSExternaFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'cobrancas_saas_externas';

    protected $fillable = [
        'fatura_saas_id',
        'gateway_cobranca_saas_id',
        'external_charge_id',
        'external_reference',
        'payment_channel',
        'status',
        'valor_emitido',
        'vencimento_emitido',
        'issued_at',
        'paid_at',
        'cancelled_at',
        'failure_reason',
        'idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExternalChargeStatus::class,
            'valor_emitido' => 'decimal:2',
            'vencimento_emitido' => 'date',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function fatura(): BelongsTo
    {
        return $this->belongsTo(FaturaSaaS::class, 'fatura_saas_id');
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(GatewayCobrancaSaaS::class, 'gateway_cobranca_saas_id');
    }

    public function retornos(): HasMany
    {
        return $this->hasMany(RetornoPagamentoSaaS::class, 'cobranca_saas_externa_id');
    }

    public function conciliacoes(): HasMany
    {
        return $this->hasMany(ConciliacaoPagamentoSaaS::class, 'cobranca_saas_externa_id');
    }
}
