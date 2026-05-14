<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\TenantThemeStatus;
use Database\Factories\TenantThemeVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantThemeVersion extends Model
{
    /** @use HasFactory<TenantThemeVersionFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'tenant_theme_versions';

    protected $fillable = [
        'brand_identity_profile_id',
        'version_label',
        'theme_tokens',
        'navigation_preferences',
        'validation_summary',
        'status',
        'published_at',
        'rolled_back_at',
    ];

    protected function casts(): array
    {
        return [
            'theme_tokens' => 'array',
            'navigation_preferences' => 'array',
            'validation_summary' => 'array',
            'status' => TenantThemeStatus::class,
            'published_at' => 'datetime',
            'rolled_back_at' => 'datetime',
        ];
    }

    public function brandIdentityProfile(): BelongsTo
    {
        return $this->belongsTo(BrandIdentityProfile::class, 'brand_identity_profile_id');
    }

    public function publicationRecords(): HasMany
    {
        return $this->hasMany(ThemePublicationRecord::class, 'tenant_theme_version_id');
    }

    public function rollbackEvidences(): HasMany
    {
        return $this->hasMany(ThemeRollbackEvidence::class, 'tenant_theme_version_id');
    }
}
