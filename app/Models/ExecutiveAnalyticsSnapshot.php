<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\ExecutiveAnalyticsSnapshotStatus;
use Database\Factories\ExecutiveAnalyticsSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecutiveAnalyticsSnapshot extends Model
{
    /** @use HasFactory<ExecutiveAnalyticsSnapshotFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'snapshot_key',
        'source_snapshot_analytics_comercial_id',
        'reference_date',
        'period_start',
        'period_end',
        'filter_hash',
        'filter_payload',
        'kpi_payload',
        'drilldown_payload',
        'snapshot_status',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'reference_date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
            'filter_payload' => 'array',
            'kpi_payload' => 'array',
            'drilldown_payload' => 'array',
            'snapshot_status' => ExecutiveAnalyticsSnapshotStatus::class,
        ];
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ExecutiveReportExport::class);
    }
}
