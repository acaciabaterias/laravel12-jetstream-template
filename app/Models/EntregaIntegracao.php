<?php

namespace App\Models;

use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EntregaIntegracao extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'entregas_integracao';

    protected $fillable = [
        'entregavel_type',
        'entregavel_id',
        'direction',
        'transport_kind',
        'target',
        'status',
        'attempt_number',
        'latency_ms',
        'http_status',
        'replayed_from_entrega_id',
        'started_at',
        'finished_at',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'direction' => IntegrationDirection::class,
            'transport_kind' => IntegrationTransportKind::class,
            'status' => IntegrationFlowStatus::class,
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function entregavel(): MorphTo
    {
        return $this->morphTo();
    }

    public function replaySource(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replayed_from_entrega_id');
    }
}
