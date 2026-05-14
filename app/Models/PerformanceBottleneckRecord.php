<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\MonitoringSeverity;
use App\Support\Operations\PerformanceBottleneckCategory;
use Database\Factories\PerformanceBottleneckRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceBottleneckRecord extends Model
{
    /** @use HasFactory<PerformanceBottleneckRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'performance_bottleneck_records';

    protected $fillable = [
        'benchmark_execution_record_id',
        'flow_name',
        'category',
        'component_name',
        'summary',
        'impact_level',
        'evidence_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'category' => PerformanceBottleneckCategory::class,
            'impact_level' => MonitoringSeverity::class,
            'evidence_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function benchmarkExecution(): BelongsTo
    {
        return $this->belongsTo(BenchmarkExecutionRecord::class, 'benchmark_execution_record_id');
    }
}
