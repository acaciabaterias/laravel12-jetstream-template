<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\CollectorHealthStatus;
use App\Support\Operations\OperationalSeverity;
use Database\Factories\OperationalAlertSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalAlertSnapshot extends Model
{
    /** @use HasFactory<OperationalAlertSnapshotFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'operational_alert_snapshots';

    protected $fillable = [
        'reference_at',
        'flow_name',
        'status',
        'severity',
        'backlog_count',
        'latency_ms',
        'failure_rate',
        'open_replays',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'reference_at' => 'datetime',
            'status' => CollectorHealthStatus::class,
            'severity' => OperationalSeverity::class,
            'backlog_count' => 'integer',
            'latency_ms' => 'integer',
            'failure_rate' => 'decimal:4',
            'open_replays' => 'integer',
            'metadata' => 'array',
        ];
    }
}
