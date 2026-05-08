<?php

namespace App\Models;

use App\Services\Integration\IntegrationStorageManager;
use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EventoOutbox extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'event_type',
        'event_version',
        'tenant_external_ref',
        'correlation_id',
        'causation_id',
        'idempotency_key',
        'origin_context',
        'status',
        'attempts',
        'occurred_at',
        'available_at',
        'dispatched_at',
        'last_error',
        'payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => IntegrationFlowStatus::class,
            'payload' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'available_at' => 'datetime',
            'dispatched_at' => 'datetime',
        ];
    }

    public function entregas(): MorphMany
    {
        return $this->morphMany(EntregaIntegracao::class, 'entregavel');
    }

    public function getConnectionName(): ?string
    {
        return app(IntegrationStorageManager::class)->currentConnection();
    }
}
