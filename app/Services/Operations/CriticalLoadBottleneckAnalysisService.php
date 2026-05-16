<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\BenchmarkExecutionRecord;
use App\Models\PerformanceBottleneckRecord;

class CriticalLoadBottleneckAnalysisService
{
    public function __construct(
        private readonly CriticalLoadEventPublisher $criticalLoadEventPublisher,
    ) {}

    /**
     * @param  array{
     *     flow_name:string,
     *     category:string,
     *     component_name:string,
     *     summary:string,
     *     impact_level:string,
     *     evidence_payload?:array<string, mixed>,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function record(BenchmarkExecutionRecord $execution, array $attributes): PerformanceBottleneckRecord
    {
        $bottleneck = PerformanceBottleneckRecord::query()->create([
            'benchmark_execution_record_id' => $execution->id,
            'flow_name' => $attributes['flow_name'],
            'category' => $attributes['category'],
            'component_name' => $attributes['component_name'],
            'summary' => $attributes['summary'],
            'impact_level' => $attributes['impact_level'],
            'evidence_payload' => $attributes['evidence_payload'] ?? [],
            'metadata' => $attributes['metadata'] ?? [],
        ]);

        if ($bottleneck->impact_level->value === 'critical') {
            $this->criticalLoadEventPublisher->publish(
                'GARGALO_CRITICO_IDENTIFICADO',
                sprintf('%s:%s:%s', $bottleneck->flow_name, $bottleneck->category->value, $bottleneck->id),
                [
                    'flow_name' => $bottleneck->flow_name,
                    'category' => $bottleneck->category->value,
                    'component_name' => $bottleneck->component_name,
                    'summary' => $bottleneck->summary,
                    'benchmark_execution_id' => $execution->id,
                ],
                ['platform'],
                [
                    'type' => 'critical-bottleneck',
                ],
            );
        }

        return $bottleneck->fresh();
    }
}
