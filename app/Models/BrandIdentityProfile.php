<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\BrandIdentityStatus;
use Database\Factories\BrandIdentityProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandIdentityProfile extends Model
{
    /** @use HasFactory<BrandIdentityProfileFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'brand_identity_profiles';

    protected $fillable = [
        'cliente_id',
        'brand_name',
        'brand_slug',
        'login_title',
        'default_font_family',
        'active_theme_version_id',
        'default_theme_tokens',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'active_theme_version_id' => 'integer',
            'default_theme_tokens' => 'array',
            'status' => BrandIdentityStatus::class,
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function themeVersions(): HasMany
    {
        return $this->hasMany(TenantThemeVersion::class, 'brand_identity_profile_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ThemeAssetRecord::class, 'brand_identity_profile_id');
    }
}
