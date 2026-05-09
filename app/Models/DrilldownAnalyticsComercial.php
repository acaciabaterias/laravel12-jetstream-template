<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DrilldownAnalyticsComercialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrilldownAnalyticsComercial extends Model
{
    /** @use HasFactory<DrilldownAnalyticsComercialFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'drilldowns_analytics_comercial';

    protected $fillable = [
        'snapshot_analytics_comercial_id',
        'source_type',
        'source_id',
        'dimension_type',
        'dimension_value',
        'metric_key',
        'metric_value',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metric_value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(SnapshotAnalyticsComercial::class, 'snapshot_analytics_comercial_id');
    }
}
