<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\CommercialAnalyticsChannelType;
use Database\Factories\MetricaPerformanceCanalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetricaPerformanceCanal extends Model
{
    /** @use HasFactory<MetricaPerformanceCanalFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'metric_channel_performance';

    protected $fillable = [
        'snapshot_analytics_comercial_id',
        'channel_type',
        'channel_name',
        'total_cases',
        'successful_cases',
        'failed_cases',
        'recovered_amount',
        'conversion_rate',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'channel_type' => CommercialAnalyticsChannelType::class,
            'total_cases' => 'integer',
            'successful_cases' => 'integer',
            'failed_cases' => 'integer',
            'recovered_amount' => 'decimal:2',
            'conversion_rate' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(SnapshotAnalyticsComercial::class, 'snapshot_analytics_comercial_id');
    }
}
