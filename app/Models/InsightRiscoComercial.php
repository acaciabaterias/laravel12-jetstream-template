<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\CommercialAnalyticsRiskType;
use Database\Factories\InsightRiscoComercialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightRiscoComercial extends Model
{
    /** @use HasFactory<InsightRiscoComercialFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'insights_risco_comercial';

    protected $fillable = [
        'snapshot_analytics_comercial_id',
        'risk_type',
        'severity',
        'total_accounts',
        'total_exposure',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'risk_type' => CommercialAnalyticsRiskType::class,
            'total_accounts' => 'integer',
            'total_exposure' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(SnapshotAnalyticsComercial::class, 'snapshot_analytics_comercial_id');
    }
}
