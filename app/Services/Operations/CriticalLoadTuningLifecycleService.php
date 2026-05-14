<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\BenchmarkExecutionRecord;
use App\Models\TuningChangeRecord;
use App\Support\Operations\BenchmarkComparisonStatus;
use App\Support\Operations\TuningLifecycleStatus;
use InvalidArgumentException;

class CriticalLoadTuningLifecycleService
{
    public function __construct(
        private readonly CriticalLoadEventPublisher $criticalLoadEventPublisher,
    ) {}

    /**
     * @param  array{
     *     flow_name:string,
     *     environment:string,
     *     change_key:string,
     *     hypothesis_summary:string,
     *     change_type:string,
     *     baseline_execution_id?:int|null,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function register(array $attributes): TuningChangeRecord
    {
        return TuningChangeRecord::query()->create([
            'flow_name' => $attributes['flow_name'],
            'environment' => $attributes['environment'],
            'change_key' => $attributes['change_key'],
            'hypothesis_summary' => $attributes['hypothesis_summary'],
            'change_type' => $attributes['change_type'],
            'applied_at' => now(),
            'status' => TuningLifecycleStatus::Pending,
            'baseline_execution_id' => $attributes['baseline_execution_id'] ?? null,
            'rollback_recommended' => false,
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }

    public function validate(TuningChangeRecord $record, BenchmarkExecutionRecord $validationExecution): TuningChangeRecord
    {
        if ($record->status !== TuningLifecycleStatus::Pending) {
            throw new InvalidArgumentException('Only pending tuning changes can be validated.');
        }

        $rollbackRecommended = $validationExecution->comparison_status === BenchmarkComparisonStatus::Regressed;

        $record->forceFill([
            'status' => TuningLifecycleStatus::Validated,
            'validation_execution_id' => $validationExecution->id,
            'rollback_recommended' => $rollbackRecommended,
        ])->save();

        if ($rollbackRecommended) {
            $this->criticalLoadEventPublisher->publish(
                'BENCHMARK_REGRESSIVO_DETECTADO',
                sprintf('%s:%s:%s', $record->flow_name, $record->change_key, $validationExecution->id),
                [
                    'flow_name' => $record->flow_name,
                    'change_key' => $record->change_key,
                    'environment' => $record->environment,
                    'validation_execution_id' => $validationExecution->id,
                    'rollback_recommended' => true,
                ],
                ['platform', 'support'],
                [
                    'type' => 'tuning-regression-validation',
                ],
            );
        }

        return $record->fresh();
    }

    public function promote(TuningChangeRecord $record): TuningChangeRecord
    {
        if ($record->status !== TuningLifecycleStatus::Validated || $record->rollback_recommended) {
            throw new InvalidArgumentException('Only validated non-regressed tuning changes can be promoted.');
        }

        $record->forceFill([
            'status' => TuningLifecycleStatus::Promoted,
        ])->save();

        return $record->fresh();
    }

    public function rollback(TuningChangeRecord $record): TuningChangeRecord
    {
        if (! $record->rollback_recommended) {
            throw new InvalidArgumentException('Rollback is only allowed for tuning changes marked as recommended for rollback.');
        }

        $record->forceFill([
            'status' => TuningLifecycleStatus::RolledBack,
        ])->save();

        $this->criticalLoadEventPublisher->publish(
            'ROLLBACK_PERFORMANCE_EXECUTADO',
            sprintf('%s:%s', $record->flow_name, $record->change_key),
            [
                'flow_name' => $record->flow_name,
                'change_key' => $record->change_key,
                'environment' => $record->environment,
                'status' => $record->status->value,
            ],
            ['platform', 'support'],
            [
                'type' => 'tuning-rollback',
            ],
        );

        return $record->fresh();
    }
}
