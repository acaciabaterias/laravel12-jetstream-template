<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Platform\PlatformCurrencyIssueResolutionStatus;
use App\Support\Platform\PlatformCurrencyIssueSeverity;
use Database\Factories\PlatformCurrencyIssueReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformCurrencyIssueReport extends Model
{
    /** @use HasFactory<PlatformCurrencyIssueReportFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'platform_currency_publication_record_id',
        'currency_code',
        'issue_type',
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
            'severity' => PlatformCurrencyIssueSeverity::class,
            'resolution_status' => PlatformCurrencyIssueResolutionStatus::class,
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(PlatformCurrencyPublicationRecord::class, 'platform_currency_publication_record_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'resolved_by');
    }
}
