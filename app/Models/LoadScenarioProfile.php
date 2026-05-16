<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LoadScenarioProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoadScenarioProfile extends Model
{
    /** @use HasFactory<LoadScenarioProfileFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'load_scenario_profiles';

    protected $fillable = [
        'flow_name',
        'scenario_name',
        'environment',
        'request_budget',
        'duration_seconds',
        'concurrency_level',
        'expected_throughput_per_minute',
        'expected_p95_latency_ms',
        'expected_error_rate',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'request_budget' => 'integer',
            'duration_seconds' => 'integer',
            'concurrency_level' => 'integer',
            'expected_throughput_per_minute' => 'integer',
            'expected_p95_latency_ms' => 'integer',
            'expected_error_rate' => 'float',
            'metadata' => 'array',
        ];
    }

    public function benchmarkExecutions(): HasMany
    {
        return $this->hasMany(BenchmarkExecutionRecord::class, 'load_scenario_profile_id');
    }
}
