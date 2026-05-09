<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LoadTestBaselineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadTestBaseline extends Model
{
    /** @use HasFactory<LoadTestBaselineFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'load_test_baselines';

    protected $fillable = [
        'scenario_name',
        'flow_name',
        'throughput_per_minute',
        'p95_latency_ms',
        'error_rate',
        'environment_notes',
        'accepted_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'throughput_per_minute' => 'integer',
            'p95_latency_ms' => 'integer',
            'error_rate' => 'decimal:4',
            'accepted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
