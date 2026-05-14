<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\MonitoringReadinessResult;
use Database\Factories\PerformanceRollbackEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceRollbackEvidence extends Model
{
    /** @use HasFactory<PerformanceRollbackEvidenceFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'performance_rollback_evidences';

    protected $fillable = [
        'tuning_change_record_id',
        'operator_user_id',
        'recorded_at',
        'result_status',
        'rollback_reason',
        'payload',
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

    public function tuningChange(): BelongsTo
    {
        return $this->belongsTo(TuningChangeRecord::class, 'tuning_change_record_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_user_id');
    }
}
