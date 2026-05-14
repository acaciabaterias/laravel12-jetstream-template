<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\PerformanceRollbackEvidence;
use App\Models\TuningChangeRecord;

class CriticalLoadRollbackEvidenceService
{
    /**
     * @param  array{
     *     operator_user_id?:int|null,
     *     result_status:string,
     *     rollback_reason:string,
     *     payload?:array<string, mixed>,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function record(TuningChangeRecord $record, array $attributes): PerformanceRollbackEvidence
    {
        return PerformanceRollbackEvidence::query()->create([
            'tuning_change_record_id' => $record->id,
            'operator_user_id' => $attributes['operator_user_id'] ?? null,
            'recorded_at' => now(),
            'result_status' => $attributes['result_status'],
            'rollback_reason' => $attributes['rollback_reason'],
            'payload' => array_merge([
                'tuning_change_record_id' => $record->id,
                'change_key' => $record->change_key,
                'flow_name' => $record->flow_name,
            ], $attributes['payload'] ?? []),
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }
}
