<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\MonitoringScrapeStatus;
use Database\Factories\MonitoringProbeSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringProbeSnapshot extends Model
{
    /** @use HasFactory<MonitoringProbeSnapshotFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'monitoring_probe_snapshots';

    protected $fillable = [
        'monitoring_target_catalog_id',
        'reference_at',
        'scrape_status',
        'latency_ms',
        'sample_count',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'reference_at' => 'datetime',
            'scrape_status' => MonitoringScrapeStatus::class,
            'latency_ms' => 'integer',
            'sample_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(MonitoringTargetCatalog::class, 'monitoring_target_catalog_id');
    }
}
