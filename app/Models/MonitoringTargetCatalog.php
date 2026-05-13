<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\MonitoringScrapeStatus;
use Database\Factories\MonitoringTargetCatalogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MonitoringTargetCatalog extends Model
{
    /** @use HasFactory<MonitoringTargetCatalogFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'monitoring_target_catalogs';

    protected $fillable = [
        'flow_name',
        'target_name',
        'environment',
        'endpoint',
        'collector_type',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => MonitoringScrapeStatus::class,
            'metadata' => 'array',
        ];
    }

    public function probeSnapshots(): HasMany
    {
        return $this->hasMany(MonitoringProbeSnapshot::class, 'monitoring_target_catalog_id');
    }

    public function latestProbeSnapshot(): HasOne
    {
        return $this->hasOne(MonitoringProbeSnapshot::class, 'monitoring_target_catalog_id')->latestOfMany('reference_at');
    }
}
