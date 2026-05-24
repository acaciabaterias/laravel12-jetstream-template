<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Fiscal\FiscalRulePublicationStatus;
use Database\Factories\FiscalRulePublicationRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalRulePublicationRecord extends Model
{
    /** @use HasFactory<FiscalRulePublicationRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'release_key',
        'status',
        'supported_scenarios',
        'catalog_snapshot',
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
            'status' => FiscalRulePublicationStatus::class,
            'supported_scenarios' => 'array',
            'catalog_snapshot' => 'array',
            'coverage_snapshot' => 'array',
            'metadata' => 'array',
            'published_at' => 'datetime',
            'rolled_back_at' => 'datetime',
        ];
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(FiscalRuleMapping::class);
    }

    public function issueReports(): HasMany
    {
        return $this->hasMany(FiscalRuleIssueReport::class);
    }

    public function taxProfiles(): HasMany
    {
        return $this->hasMany(FiscalTaxProfile::class, 'fiscal_rule_publication_record_id');
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
