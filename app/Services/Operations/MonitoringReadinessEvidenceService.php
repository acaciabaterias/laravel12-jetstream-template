<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\DashboardProvisioningRecord;
use App\Models\MonitoringReadinessEvidence;
use Illuminate\Support\Carbon;

class MonitoringReadinessEvidenceService
{
    /**
     * @param  array{
     *     environment:string,
     *     evidence_type:string,
     *     operator_user_id?:int|null,
     *     recorded_at?:Carbon|string|null,
     *     result_status:string,
     *     payload?:array<string, mixed>,
     *     notes?:string|null,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function record(array $attributes): MonitoringReadinessEvidence
    {
        return MonitoringReadinessEvidence::query()->create([
            'environment' => $attributes['environment'],
            'evidence_type' => $attributes['evidence_type'],
            'operator_user_id' => $attributes['operator_user_id'] ?? null,
            'recorded_at' => $attributes['recorded_at'] ?? now(),
            'result_status' => $attributes['result_status'],
            'payload' => $attributes['payload'] ?? [],
            'notes' => $attributes['notes'] ?? null,
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }

    /**
     * @param  array{
     *     evidence_type:string,
     *     operator_user_id?:int|null,
     *     result_status:string,
     *     notes?:string|null,
     *     payload?:array<string, mixed>,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function recordForProvisioning(DashboardProvisioningRecord $record, array $attributes): MonitoringReadinessEvidence
    {
        return $this->record([
            'environment' => $record->environment,
            'evidence_type' => $attributes['evidence_type'],
            'operator_user_id' => $attributes['operator_user_id'] ?? null,
            'result_status' => $attributes['result_status'],
            'notes' => $attributes['notes'] ?? null,
            'payload' => array_merge([
                'dashboard_provisioning_record_id' => $record->id,
                'package_name' => $record->package_name,
                'version' => $record->version,
                'rollback_version' => $record->rollback_version,
            ], $attributes['payload'] ?? []),
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }
}
