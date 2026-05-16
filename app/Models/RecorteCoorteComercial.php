<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RecorteCoorteComercialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecorteCoorteComercial extends Model
{
    /** @use HasFactory<RecorteCoorteComercialFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'recortes_coorte_comercial';

    protected $fillable = [
        'snapshot_analytics_comercial_id',
        'cohort_label',
        'cohort_start_date',
        'cohort_end_date',
        'active_subscriptions',
        'cancelled_subscriptions',
        'recovered_subscriptions',
        'delinquent_subscriptions',
        'mrr_amount',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'cohort_start_date' => 'date',
            'cohort_end_date' => 'date',
            'active_subscriptions' => 'integer',
            'cancelled_subscriptions' => 'integer',
            'recovered_subscriptions' => 'integer',
            'delinquent_subscriptions' => 'integer',
            'mrr_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(SnapshotAnalyticsComercial::class, 'snapshot_analytics_comercial_id');
    }
}
