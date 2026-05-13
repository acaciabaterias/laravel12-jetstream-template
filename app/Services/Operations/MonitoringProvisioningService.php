<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\DashboardProvisioningRecord;
use App\Support\Operations\MonitoringProvisioningStatus;
use InvalidArgumentException;

class MonitoringProvisioningService
{
    public function __construct(
        private readonly MonitoringConsolidationEventPublisher $monitoringConsolidationEventPublisher,
    ) {}

    /**
     * @param  array{
     *     package_name:string,
     *     version:string,
     *     environment:string,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function register(array $attributes): DashboardProvisioningRecord
    {
        return DashboardProvisioningRecord::query()->create([
            'package_name' => $attributes['package_name'],
            'version' => $attributes['version'],
            'environment' => $attributes['environment'],
            'status' => MonitoringProvisioningStatus::Pending,
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }

    /**
     * @param  array{metadata?:array<string, mixed>}  $attributes
     */
    public function markProvisioned(DashboardProvisioningRecord $record, array $attributes = []): DashboardProvisioningRecord
    {
        $record->forceFill([
            'applied_at' => now(),
            'status' => MonitoringProvisioningStatus::Applied,
            'metadata' => array_merge($record->metadata ?? [], $attributes['metadata'] ?? []),
        ])->save();

        $this->monitoringConsolidationEventPublisher->publish(
            'DASHBOARD_MONITORAMENTO_ATUALIZADO',
            sprintf('%s:%s:%s', $record->package_name, $record->version, $record->environment),
            [
                'package_name' => $record->package_name,
                'version' => $record->version,
                'environment' => $record->environment,
                'status' => $record->status->value,
                'applied_at' => $record->applied_at?->toAtomString(),
            ],
            ['platform', 'support'],
            [
                'type' => 'monitoring-dashboard-provisioned',
            ],
        );

        return $record->fresh();
    }

    /**
     * @param  array{metadata?:array<string, mixed>}  $attributes
     */
    public function markValidated(DashboardProvisioningRecord $record, array $attributes = []): DashboardProvisioningRecord
    {
        if ($record->status !== MonitoringProvisioningStatus::Applied || $record->applied_at === null) {
            throw new InvalidArgumentException('Only applied dashboard packages can be validated.');
        }

        $record->forceFill([
            'validated_at' => now(),
            'metadata' => array_merge($record->metadata ?? [], $attributes['metadata'] ?? []),
        ])->save();

        $this->monitoringConsolidationEventPublisher->publish(
            'DASHBOARD_MONITORAMENTO_ATUALIZADO',
            sprintf('%s:%s:%s', $record->package_name, $record->version, $record->environment),
            [
                'package_name' => $record->package_name,
                'version' => $record->version,
                'environment' => $record->environment,
                'status' => $record->status->value,
                'validated_at' => $record->validated_at?->toAtomString(),
            ],
            ['platform', 'support'],
            [
                'type' => 'monitoring-dashboard-validated',
            ],
        );

        return $record->fresh();
    }

    /**
     * @param  array{
     *     rollback_version:string,
     *     metadata?:array<string, mixed>
     * }  $attributes
     */
    public function rollback(DashboardProvisioningRecord $record, array $attributes): DashboardProvisioningRecord
    {
        if ($record->status !== MonitoringProvisioningStatus::Applied) {
            throw new InvalidArgumentException('Only applied dashboard packages can be rolled back.');
        }

        $rollbackVersion = trim($attributes['rollback_version']);

        if ($rollbackVersion === '') {
            throw new InvalidArgumentException('Rollback version is required.');
        }

        $record->forceFill([
            'rollback_version' => $rollbackVersion,
            'status' => MonitoringProvisioningStatus::RolledBack,
            'validated_at' => null,
            'metadata' => array_merge($record->metadata ?? [], $attributes['metadata'] ?? []),
        ])->save();

        $this->monitoringConsolidationEventPublisher->publish(
            'ROLLBACK_MONITORAMENTO_EXECUTADO',
            sprintf('%s:%s:%s', $record->package_name, $record->version, $record->environment),
            [
                'package_name' => $record->package_name,
                'version' => $record->version,
                'environment' => $record->environment,
                'rollback_version' => $record->rollback_version,
                'status' => $record->status->value,
            ],
            ['platform', 'support'],
            [
                'type' => 'monitoring-dashboard-rollback',
            ],
        );

        return $record->fresh();
    }
}
