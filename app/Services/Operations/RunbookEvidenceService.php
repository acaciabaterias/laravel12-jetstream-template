<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\OperationalIncidentRecord;
use App\Models\RunbookExecutionEvidence;
use App\Support\Operations\OperationalIncidentStatus;

class RunbookEvidenceService
{
    /**
     * @param array{
     *     execution_type:string,
     *     operator_user_id:?int,
     *     started_at:mixed,
     *     finished_at:mixed,
     *     result_status:string,
     *     evidence_payload?:array<string, mixed>,
     *     notes:?string,
     *     metadata?:array<string, mixed>
     * } $attributes
     */
    public function record(OperationalIncidentRecord $incident, array $attributes): RunbookExecutionEvidence
    {
        $evidence = $incident->evidences()->create([
            'execution_type' => $attributes['execution_type'],
            'operator_user_id' => $attributes['operator_user_id'],
            'started_at' => $attributes['started_at'],
            'finished_at' => $attributes['finished_at'],
            'result_status' => $attributes['result_status'],
            'evidence_payload' => $attributes['evidence_payload'] ?? [],
            'notes' => $attributes['notes'],
            'metadata' => $attributes['metadata'] ?? [],
        ]);

        if ($incident->status === OperationalIncidentStatus::Open) {
            $incident->forceFill([
                'status' => OperationalIncidentStatus::Acknowledged->value,
                'acknowledged_at' => $incident->acknowledged_at ?? now(),
            ])->save();
        }

        return $evidence->refresh();
    }
}
