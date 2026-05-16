<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\TuningLifecycleStatus;
use Database\Factories\TuningChangeRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TuningChangeRecord extends Model
{
    /** @use HasFactory<TuningChangeRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'tuning_change_records';

    protected $fillable = [
        'flow_name',
        'environment',
        'change_key',
        'hypothesis_summary',
        'change_type',
        'applied_at',
        'status',
        'baseline_execution_id',
        'validation_execution_id',
        'rollback_recommended',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'status' => TuningLifecycleStatus::class,
            'rollback_recommended' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function baselineExecution(): BelongsTo
    {
        return $this->belongsTo(BenchmarkExecutionRecord::class, 'baseline_execution_id');
    }

    public function validationExecution(): BelongsTo
    {
        return $this->belongsTo(BenchmarkExecutionRecord::class, 'validation_execution_id');
    }

    public function rollbackEvidences(): HasMany
    {
        return $this->hasMany(PerformanceRollbackEvidence::class, 'tuning_change_record_id');
    }
}
