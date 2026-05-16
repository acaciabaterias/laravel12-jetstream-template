<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\TenantThemeVersion;
use App\Models\ThemeRollbackEvidence;
use Illuminate\Support\Collection;

class AdvancedWhiteLabelRollbackService
{
    public function __construct(
        private readonly AdvancedWhiteLabelPublicationService $advancedWhiteLabelPublicationService,
        private readonly AdvancedWhiteLabelEventPublisher $advancedWhiteLabelEventPublisher,
    ) {}

    public function rollback(TenantThemeVersion $themeVersion, string $reason, ?int $operatorId): ThemeRollbackEvidence
    {
        $themeVersion->loadMissing(['brandIdentityProfile.cliente', 'brandIdentityProfile.assets']);

        $restoredTheme = $this->findRestoredThemeVersion(
            TenantThemeVersion::query()
                ->where('brand_identity_profile_id', $themeVersion->brand_identity_profile_id)
                ->where('status', 'published')
                ->latest('published_at')
                ->get(),
            $themeVersion->id
        );

        $this->advancedWhiteLabelPublicationService->syncAppliedBranding(
            $themeVersion->brandIdentityProfile,
            $restoredTheme
        );

        $themeVersion->forceFill([
            'status' => 'rolled_back',
            'rolled_back_at' => now(),
        ])->save();

        $themeVersion->brandIdentityProfile->forceFill([
            'active_theme_version_id' => $restoredTheme?->id,
            'status' => $restoredTheme ? 'active' : 'draft',
        ])->save();

        $evidence = ThemeRollbackEvidence::query()->create([
            'tenant_theme_version_id' => $themeVersion->id,
            'restored_theme_version_id' => $restoredTheme?->id,
            'operator_id' => $operatorId,
            'reason' => $reason,
            'evidence_payload' => [
                'brand_identity_profile_id' => $themeVersion->brand_identity_profile_id,
                'restored_theme_version_id' => $restoredTheme?->id,
                'used_default_fallback' => $restoredTheme === null,
            ],
            'rolled_back_at' => now(),
        ]);

        $this->advancedWhiteLabelEventPublisher->publish(
            'ROLLBACK_TEMA_WHITE_LABEL_EXECUTADO',
            (string) $themeVersion->id,
            [
                'tenant_id' => $themeVersion->brandIdentityProfile->cliente_id,
                'tenant_subdomain' => $themeVersion->brandIdentityProfile->cliente?->subdominio,
                'tenant_theme_version_id' => $themeVersion->id,
                'restored_theme_version_id' => $restoredTheme?->id,
                'reason' => $reason,
                'rolled_back_at' => $evidence->rolled_back_at?->toAtomString(),
            ],
            ['erp-core', 'admin-dashboard']
        );

        return $evidence;
    }

    /**
     * @param  Collection<int, TenantThemeVersion>  $publishedThemes
     */
    public function findRestoredThemeVersion(Collection $publishedThemes, int $currentThemeId): ?TenantThemeVersion
    {
        return $publishedThemes
            ->filter(fn (TenantThemeVersion $themeVersion): bool => $themeVersion->id !== $currentThemeId)
            ->sortByDesc(fn (TenantThemeVersion $themeVersion): int => $themeVersion->published_at?->getTimestamp() ?? 0)
            ->first();
    }
}
