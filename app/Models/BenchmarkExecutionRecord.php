<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\BenchmarkComparisonStatus;
use App\Support\Operations\BenchmarkExecutionStatus;
use Database\Factories\BenchmarkExecutionRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BenchmarkExecutionRecord extends Model
{
    /** @use HasFactory<BenchmarkExecutionRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'benchmark_execution_records';

    protected $fillable = [
        'load_scenario_profile_id',
        'started_at',
        'finished_at',
        'throughput_per_minute',
        'p95_latency_ms',
        'error_rate',
        'status',
        'comparison_status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'throughput_per_minute' => 'integer',
            'p95_latency_ms' => 'integer',
            'error_rate' => 'float',
            'status' => BenchmarkExecutionStatus::class,
            'comparison_status' => BenchmarkComparisonStatus::class,
            'metadata' => 'array',
        ];
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(LoadScenarioProfile::class, 'load_scenario_profile_id');
    }

    public function bottlenecks(): HasMany
    {
        return $this->hasMany(PerformanceBottleneckRecord::class, 'benchmark_execution_record_id');
    }
}
