<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\MonitoringReadinessResult;
use Database\Factories\MonitoringReadinessEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringReadinessEvidence extends Model
{
    /** @use HasFactory<MonitoringReadinessEvidenceFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'monitoring_readiness_evidences';

    protected $fillable = [
        'environment',
        'evidence_type',
        'operator_user_id',
        'recorded_at',
        'result_status',
        'payload',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'result_status' => MonitoringReadinessResult::class,
            'payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_user_id');
    }
}
