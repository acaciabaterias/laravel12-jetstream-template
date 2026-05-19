<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Database\Factories\PlatformCurrencyPublicationRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformCurrencyPublicationRecord extends Model
{
    /** @use HasFactory<PlatformCurrencyPublicationRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'release_key',
        'status',
        'base_currency_code',
        'default_currency_code',
        'supported_currencies',
        'rate_snapshot',
        'coverage_snapshot',
        'published_by',
        'rolled_back_by',
        'published_at',
        'rolled_back_at',
        'superseded_by_publication_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlatformCurrencyPublicationStatus::class,
            'supported_currencies' => 'array',
            'rate_snapshot' => 'array',
            'coverage_snapshot' => 'array',
            'metadata' => 'array',
            'published_at' => 'datetime',
            'rolled_back_at' => 'datetime',
        ];
    }

    public function rateEntries(): HasMany
    {
        return $this->hasMany(PlatformCurrencyRateEntry::class);
    }

    public function issueReports(): HasMany
    {
        return $this->hasMany(PlatformCurrencyIssueReport::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'published_by');
    }

    public function rollbackOperator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'rolled_back_by');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_publication_id');
    }
}
