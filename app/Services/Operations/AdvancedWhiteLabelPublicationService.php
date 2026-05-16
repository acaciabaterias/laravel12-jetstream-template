<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\BrandIdentityProfile;
use App\Models\TenantThemeVersion;
use App\Models\ThemePublicationRecord;
use App\Models\WhiteLabelConfig;
use Illuminate\Support\Arr;

class AdvancedWhiteLabelPublicationService
{
    public function __construct(
        private readonly AdvancedWhiteLabelTokenValidator $advancedWhiteLabelTokenValidator,
        private readonly AdvancedWhiteLabelEventPublisher $advancedWhiteLabelEventPublisher,
    ) {}

    public function publish(TenantThemeVersion $themeVersion, string $environment, ?int $operatorId): ThemePublicationRecord
    {
        $themeVersion->loadMissing(['brandIdentityProfile.cliente', 'brandIdentityProfile.assets']);

        $validation = $this->advancedWhiteLabelTokenValidator->validate($themeVersion->theme_tokens ?? []);

        $publication = ThemePublicationRecord::query()->create([
            'tenant_theme_version_id' => $themeVersion->id,
            'environment' => $environment,
            'operator_id' => $operatorId,
            'validation_passed' => $validation['passed'],
            'validation_messages' => $validation['messages'],
            'published_snapshot' => $themeVersion->theme_tokens ?? [],
            'status' => $validation['passed'] ? 'published' : 'rejected',
            'published_at' => $validation['passed'] ? now() : null,
        ]);

        $themeVersion->forceFill([
            'validation_summary' => [
                'passed' => $validation['passed'],
                'messages' => $validation['messages'],
                'contrast_ratio' => $validation['contrast_ratio'],
            ],
            'status' => $validation['passed'] ? 'published' : 'draft',
            'published_at' => $validation['passed'] ? now() : $themeVersion->published_at,
        ])->save();

        if (! $validation['passed']) {
            return $publication;
        }

        $profile = $themeVersion->brandIdentityProfile;
        $this->syncAppliedBranding($profile, $themeVersion);

        $profile->forceFill([
            'active_theme_version_id' => $themeVersion->id,
            'status' => 'active',
        ])->save();

        $this->advancedWhiteLabelEventPublisher->publish(
            'TEMA_WHITE_LABEL_PUBLICADO',
            (string) $themeVersion->id,
            [
                'tenant_id' => $profile->cliente_id,
                'tenant_subdomain' => $profile->cliente?->subdominio,
                'brand_identity_profile_id' => $profile->id,
                'tenant_theme_version_id' => $themeVersion->id,
                'version_label' => $themeVersion->version_label,
                'environment' => $environment,
                'published_at' => $publication->published_at?->toAtomString(),
            ],
            ['erp-core', 'admin-dashboard']
        );

        return $publication->fresh() ?? $publication;
    }

    public function syncAppliedBranding(BrandIdentityProfile $profile, ?TenantThemeVersion $themeVersion): WhiteLabelConfig
    {
        $tokens = $themeVersion?->theme_tokens ?? $profile->default_theme_tokens ?? [];
        $navigationPreferences = $themeVersion?->navigation_preferences ?? [];
        $logoUrl = $profile->assets->firstWhere('asset_type', 'logo_primary')?->storage_reference;
        $faviconUrl = $profile->assets->firstWhere('asset_type', 'favicon')?->storage_reference;

        return WhiteLabelConfig::query()->updateOrCreate(
            ['cliente_id' => $profile->cliente_id],
            [
                'logo_url' => $logoUrl,
                'favicon_url' => $faviconUrl,
                'cor_primaria' => Arr::get($tokens, 'primary', '#123B66'),
                'cor_secundaria' => Arr::get($tokens, 'secondary', '#F59E0B'),
                'cor_fundo' => Arr::get($tokens, 'surface', '#F8FAFC'),
                'titulo_login' => $profile->login_title ?: $profile->brand_name,
                'custom_css' => null,
                'custom_js' => null,
                'template_nome' => (string) Arr::get($navigationPreferences, 'template_name', config('advanced_white_label.fallback.template_name', 'default')),
                'mostrar_marca_plataforma' => (bool) Arr::get($navigationPreferences, 'show_platform_brand', config('advanced_white_label.fallback.show_platform_brand', true)),
            ]
        );
    }
}
