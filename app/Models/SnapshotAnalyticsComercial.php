<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\CommercialAnalyticsRebuildStatus;
use App\Support\Billing\CommercialAnalyticsSnapshotType;
use Database\Factories\SnapshotAnalyticsComercialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SnapshotAnalyticsComercial extends Model
{
    /** @use HasFactory<SnapshotAnalyticsComercialFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'snapshots_analytics_comercial';

    protected $fillable = [
        'snapshot_type',
        'reference_date',
        'period_start',
        'period_end',
        'rebuild_status',
        'mrr_amount',
        'churn_count',
        'churn_rate',
        'delinquent_count',
        'recovered_count',
        'recovered_amount',
        'blocked_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_type' => CommercialAnalyticsSnapshotType::class,
            'reference_date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
            'rebuild_status' => CommercialAnalyticsRebuildStatus::class,
            'mrr_amount' => 'decimal:2',
            'churn_count' => 'integer',
            'churn_rate' => 'decimal:4',
            'delinquent_count' => 'integer',
            'recovered_count' => 'integer',
            'recovered_amount' => 'decimal:2',
            'blocked_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(RecorteCoorteComercial::class, 'snapshot_analytics_comercial_id');
    }

    public function channelMetrics(): HasMany
    {
        return $this->hasMany(MetricaPerformanceCanal::class, 'snapshot_analytics_comercial_id');
    }

    public function riskInsights(): HasMany
    {
        return $this->hasMany(InsightRiscoComercial::class, 'snapshot_analytics_comercial_id');
    }

    public function drilldowns(): HasMany
    {
        return $this->hasMany(DrilldownAnalyticsComercial::class, 'snapshot_analytics_comercial_id');
    }
}
