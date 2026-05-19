<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Platform\PlatformLocalePublicationStatus;
use Database\Factories\PlatformLocalePublicationRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformLocalePublicationRecord extends Model
{
    /** @use HasFactory<PlatformLocalePublicationRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'release_key',
        'status',
        'default_locale',
        'fallback_locale',
        'supported_locales',
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
            'status' => PlatformLocalePublicationStatus::class,
            'supported_locales' => 'array',
            'coverage_snapshot' => 'array',
            'metadata' => 'array',
            'published_at' => 'datetime',
            'rolled_back_at' => 'datetime',
        ];
    }

    public function missingKeyReports(): HasMany
    {
        return $this->hasMany(PlatformLocaleMissingKeyReport::class);
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
