<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlatformCurrencyRateEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformCurrencyRateEntry extends Model
{
    /** @use HasFactory<PlatformCurrencyRateEntryFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'platform_currency_publication_record_id',
        'currency_code',
        'rate_against_base',
        'inverse_rate',
        'effective_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'rate_against_base' => 'decimal:8',
            'inverse_rate' => 'decimal:8',
            'effective_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(PlatformCurrencyPublicationRecord::class, 'platform_currency_publication_record_id');
    }
}
