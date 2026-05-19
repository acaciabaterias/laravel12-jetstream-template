<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Platform\PlatformLocaleMissingKeyResolutionStatus;
use App\Support\Platform\PlatformLocaleMissingKeySeverity;
use Database\Factories\PlatformLocaleMissingKeyReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformLocaleMissingKeyReport extends Model
{
    /** @use HasFactory<PlatformLocaleMissingKeyReportFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'platform_locale_publication_record_id',
        'locale_code',
        'translation_key',
        'context_group',
        'severity',
        'resolution_status',
        'detected_at',
        'resolved_at',
        'resolved_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'severity' => PlatformLocaleMissingKeySeverity::class,
            'resolution_status' => PlatformLocaleMissingKeyResolutionStatus::class,
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(PlatformLocalePublicationRecord::class, 'platform_locale_publication_record_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'resolved_by');
    }
}
