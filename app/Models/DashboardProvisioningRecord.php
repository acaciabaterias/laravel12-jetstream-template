<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\MonitoringProvisioningStatus;
use Database\Factories\DashboardProvisioningRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardProvisioningRecord extends Model
{
    /** @use HasFactory<DashboardProvisioningRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'dashboard_provisioning_records';

    protected $fillable = [
        'package_name',
        'version',
        'environment',
        'applied_at',
        'validated_at',
        'rollback_version',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'validated_at' => 'datetime',
            'status' => MonitoringProvisioningStatus::class,
            'metadata' => 'array',
        ];
    }
}
