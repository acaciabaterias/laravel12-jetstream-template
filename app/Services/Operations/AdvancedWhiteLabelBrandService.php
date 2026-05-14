<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\BrandIdentityProfile;
use App\Models\TenantThemeVersion;
use App\Models\ThemeAssetRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdvancedWhiteLabelBrandService
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, string|null>  $assetReferences
     */
    public function registerProfile(array $attributes, array $assetReferences = []): BrandIdentityProfile
    {
        $profile = BrandIdentityProfile::query()->updateOrCreate(
            ['cliente_id' => $attributes['cliente_id']],
            [
                'brand_name' => $attributes['brand_name'],
                'brand_slug' => $attributes['brand_slug'],
                'login_title' => $attributes['login_title'] ?? $attributes['brand_name'],
                'default_font_family' => $attributes['default_font_family'] ?? 'Poppins',
                'default_theme_tokens' => $attributes['default_theme_tokens'] ?? [],
                'status' => $attributes['status'] ?? 'draft',
                'notes' => $attributes['notes'] ?? null,
            ]
        );

        foreach ($assetReferences as $assetType => $storageReference) {
            if (! filled($storageReference)) {
                continue;
            }

            ThemeAssetRecord::query()->updateOrCreate(
                [
                    'brand_identity_profile_id' => $profile->id,
                    'asset_type' => $assetType,
                ],
                [
                    'storage_reference' => $storageReference,
                    'mime_type' => 'image/png',
                    'checksum' => sha1((string) $storageReference),
                    'status' => 'active',
                ]
            );
        }

        return $profile->fresh(['cliente', 'assets']) ?? $profile;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function registerThemeVersion(BrandIdentityProfile $profile, array $attributes): TenantThemeVersion
    {
        return $profile->themeVersions()->create([
            'version_label' => $attributes['version_label'],
            'theme_tokens' => $attributes['theme_tokens'],
            'navigation_preferences' => $attributes['navigation_preferences'] ?? [],
            'validation_summary' => [],
            'status' => 'draft',
        ]);
    }

    public function latestProfiles(?int $tenantId = null, int $limit = 25): Collection
    {
        return BrandIdentityProfile::query()
            ->with(['cliente', 'assets', 'themeVersions'])
            ->when($tenantId !== null, fn ($query) => $query->where('cliente_id', $tenantId))
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array{tenant_id?:int|null,status?:string|null,publication_status?:string|null}  $filters
     */
    public function themeVersions(array $filters = [], int $limit = 25): LengthAwarePaginator
    {
        return TenantThemeVersion::query()
            ->with(['brandIdentityProfile.cliente', 'publicationRecords'])
            ->when(($filters['tenant_id'] ?? null) !== null, fn ($query) => $query->whereHas('brandIdentityProfile', fn ($profileQuery) => $profileQuery->where('cliente_id', $filters['tenant_id'])))
            ->when(($filters['status'] ?? '') !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when(($filters['publication_status'] ?? '') !== '', fn ($query) => $query->whereHas('publicationRecords', fn ($publicationQuery) => $publicationQuery->where('status', $filters['publication_status'])))
            ->latest('updated_at')
            ->paginate($limit);
    }

    /**
     * @return array<string, int>
     */
    public function summarize(): array
    {
        return [
            'profiles' => BrandIdentityProfile::query()->count(),
            'active_profiles' => BrandIdentityProfile::query()->where('status', 'active')->count(),
            'draft_themes' => TenantThemeVersion::query()->where('status', 'draft')->count(),
            'published_themes' => TenantThemeVersion::query()->where('status', 'published')->count(),
        ];
    }
}
