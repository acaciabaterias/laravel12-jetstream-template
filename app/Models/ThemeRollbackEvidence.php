<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ThemeRollbackEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeRollbackEvidence extends Model
{
    /** @use HasFactory<ThemeRollbackEvidenceFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'theme_rollback_evidences';

    protected $fillable = [
        'tenant_theme_version_id',
        'restored_theme_version_id',
        'operator_id',
        'reason',
        'evidence_payload',
        'rolled_back_at',
    ];

    protected function casts(): array
    {
        return [
            'restored_theme_version_id' => 'integer',
            'operator_id' => 'integer',
            'evidence_payload' => 'array',
            'rolled_back_at' => 'datetime',
        ];
    }

    public function themeVersion(): BelongsTo
    {
        return $this->belongsTo(TenantThemeVersion::class, 'tenant_theme_version_id');
    }

    public function restoredThemeVersion(): BelongsTo
    {
        return $this->belongsTo(TenantThemeVersion::class, 'restored_theme_version_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_id');
    }
}
