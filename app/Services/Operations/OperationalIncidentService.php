<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\OperationalIncidentRecord;
use App\Support\Operations\OperationalIncidentStatus;
use App\Support\Operations\OperationalSeverity;

class OperationalIncidentService
{
    /**
     * @param  array{incident_key:string,flow_name:string,severity:string,summary:string,metadata?:array<string, mixed>}  $attributes
     */
    public function open(array $attributes): OperationalIncidentRecord
    {
        return OperationalIncidentRecord::query()->create([
            'incident_key' => $attributes['incident_key'],
            'flow_name' => $attributes['flow_name'],
            'severity' => $attributes['severity'],
            'status' => OperationalIncidentStatus::Open->value,
            'opened_at' => now(),
            'summary' => $attributes['summary'],
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }

    public function acknowledge(OperationalIncidentRecord $incident): OperationalIncidentRecord
    {
        $incident->forceFill([
            'status' => OperationalIncidentStatus::Acknowledged->value,
            'acknowledged_at' => $incident->acknowledged_at ?? now(),
        ])->save();

        return $incident->refresh();
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function resolve(OperationalIncidentRecord $incident, array $metadata = []): OperationalIncidentRecord
    {
        $incident->forceFill([
            'status' => OperationalIncidentStatus::Resolved->value,
            'resolved_at' => now(),
            'metadata' => array_merge($incident->metadata ?? [], $metadata),
        ])->save();

        return $incident->refresh();
    }

    /**
     * @param  array<string, mixed>  $validationEvidence
     */
    public function close(OperationalIncidentRecord $incident, array $validationEvidence): OperationalIncidentRecord
    {
        $incident->loadCount([
            'evidences as completed_evidences_count' => fn ($query) => $query->whereIn('result_status', ['success', 'partial']),
        ]);

        if ($incident->status !== OperationalIncidentStatus::Resolved) {
            throw new \DomainException('Incident must be resolved before closure.');
        }

        if (($incident->completed_evidences_count ?? 0) < 1) {
            throw new \DomainException('Incident closure requires at least one completed runbook evidence.');
        }

        if (($validationEvidence['post_validation_passed'] ?? false) !== true) {
            throw new \DomainException('Incident closure requires explicit post-validation evidence.');
        }

        $incident->forceFill([
            'status' => OperationalIncidentStatus::Closed->value,
            'metadata' => array_merge($incident->metadata ?? [], [
                'closure_validation' => $validationEvidence,
                'closed_at' => now()->toAtomString(),
            ]),
        ])->save();

        return $incident->refresh();
    }

    public function openForSnapshot(string $flowName, OperationalSeverity $severity, string $summary, array $metadata = []): OperationalIncidentRecord
    {
        return $this->open([
            'incident_key' => sprintf('%s-%s-%s', $flowName, $severity->value, now()->format('YmdHis')),
            'flow_name' => $flowName,
            'severity' => $severity->value,
            'summary' => $summary,
            'metadata' => $metadata,
        ]);
    }
}
