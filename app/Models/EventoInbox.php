<?php

namespace App\Models;

use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EventoInbox extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'event_type',
        'event_version',
        'tenant_external_ref',
        'producer',
        'correlation_id',
        'causation_id',
        'external_event_id',
        'idempotency_key',
        'status',
        'duplicate_detected',
        'occurred_at',
        'received_at',
        'consumed_at',
        'last_error',
        'payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => IntegrationFlowStatus::class,
            'duplicate_detected' => 'boolean',
            'payload' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'received_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function entregas(): MorphMany
    {
        return $this->morphMany(EntregaIntegracao::class, 'entregavel');
    }
}
