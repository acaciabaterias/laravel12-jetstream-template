<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\BrandIdentityProfile;
use App\Models\TenantThemeVersion;
use App\Models\ThemePublicationRecord;
use App\Models\ThemeRollbackEvidence;

class AdvancedWhiteLabelInspectionService
{
    public function __construct(
        private readonly AdvancedWhiteLabelBrandService $advancedWhiteLabelBrandService,
    ) {}

    /**
     * @param  array{tenant_id?:int|null,status?:string|null,publication_status?:string|null,limit?:int|null}  $filters
     * @return array{
     *     summary: array<string, int>,
     *     profiles: array<int, array<string, mixed>>,
     *     themes: array<int, array<string, mixed>>,
     *     publications: array<int, array<string, mixed>>,
     *     rollbacks: array<int, array<string, mixed>>
     * }
     */
    public function inspect(array $filters = []): array
    {
        $tenantId = isset($filters['tenant_id']) ? (int) $filters['tenant_id'] : null;
        $limit = (int) ($filters['limit'] ?? 25);
        $themes = $this->advancedWhiteLabelBrandService->themeVersions([
            'tenant_id' => $tenantId,
            'status' => (string) ($filters['status'] ?? ''),
            'publication_status' => (string) ($filters['publication_status'] ?? ''),
        ], $limit);
        $profiles = $this->advancedWhiteLabelBrandService->latestProfiles($tenantId, $limit);
        $publications = ThemePublicationRecord::query()
            ->with(['themeVersion.brandIdentityProfile.cliente', 'operator'])
            ->when($tenantId !== null, fn ($query) => $query->whereHas('themeVersion.brandIdentityProfile', fn ($profileQuery) => $profileQuery->where('cliente_id', $tenantId)))
            ->when(($filters['publication_status'] ?? '') !== '', fn ($query) => $query->where('status', $filters['publication_status']))
            ->latest('updated_at')
            ->limit($limit)
            ->get();
        $rollbacks = ThemeRollbackEvidence::query()
            ->with(['themeVersion.brandIdentityProfile.cliente', 'restoredThemeVersion', 'operator'])
            ->when($tenantId !== null, fn ($query) => $query->whereHas('themeVersion.brandIdentityProfile', fn ($profileQuery) => $profileQuery->where('cliente_id', $tenantId)))
            ->latest('rolled_back_at')
            ->limit($limit)
            ->get();

        return [
            'summary' => $this->advancedWhiteLabelBrandService->summarize(),
            'profiles' => $profiles->map(fn (BrandIdentityProfile $profile): array => [
                'id' => $profile->id,
                'tenant_id' => $profile->cliente_id,
                'tenant_subdomain' => $profile->cliente?->subdominio,
                'brand_name' => $profile->brand_name,
                'brand_slug' => $profile->brand_slug,
                'login_title' => $profile->login_title,
                'status' => $profile->status->value,
                'active_theme_version_id' => $profile->active_theme_version_id,
            ])->values()->all(),
            'themes' => $themes->getCollection()->map(fn (TenantThemeVersion $theme): array => [
                'id' => $theme->id,
                'tenant_id' => $theme->brandIdentityProfile?->cliente_id,
                'tenant_subdomain' => $theme->brandIdentityProfile?->cliente?->subdominio,
                'brand_name' => $theme->brandIdentityProfile?->brand_name,
                'version_label' => $theme->version_label,
                'status' => $theme->status->value,
                'published_at' => $theme->published_at?->toAtomString(),
                'validation_summary' => $theme->validation_summary,
            ])->values()->all(),
            'publications' => $publications->map(fn (ThemePublicationRecord $publication): array => [
                'id' => $publication->id,
                'tenant_theme_version_id' => $publication->tenant_theme_version_id,
                'tenant_subdomain' => $publication->themeVersion?->brandIdentityProfile?->cliente?->subdominio,
                'status' => $publication->status->value,
                'environment' => $publication->environment,
                'validation_passed' => $publication->validation_passed,
                'validation_messages' => $publication->validation_messages,
                'published_at' => $publication->published_at?->toAtomString(),
            ])->values()->all(),
            'rollbacks' => $rollbacks->map(fn (ThemeRollbackEvidence $rollback): array => [
                'id' => $rollback->id,
                'tenant_theme_version_id' => $rollback->tenant_theme_version_id,
                'tenant_subdomain' => $rollback->themeVersion?->brandIdentityProfile?->cliente?->subdominio,
                'restored_theme_version_id' => $rollback->restored_theme_version_id,
                'reason' => $rollback->reason,
                'operator' => $rollback->operator?->name,
                'rolled_back_at' => $rollback->rolled_back_at?->toAtomString(),
            ])->values()->all(),
        ];
    }
}
