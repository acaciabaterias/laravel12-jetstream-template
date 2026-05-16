<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\BrandIdentityProfile;
use App\Models\TenantThemeVersion;
use App\Services\Operations\AdvancedWhiteLabelBrandService;
use App\Services\Operations\AdvancedWhiteLabelInspectionService;
use App\Services\Operations\AdvancedWhiteLabelPublicationService;
use App\Services\Operations\AdvancedWhiteLabelRollbackService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class AdvancedWhiteLabelDashboard extends Component
{
    #[Url(as: 'tenant')]
    public int $tenantIdFilter = 0;

    #[Url(as: 'status')]
    public string $profileStatusFilter = '';

    #[Url(as: 'publication')]
    public string $publicationStatusFilter = '';

    public int $selectedTenantId = 0;

    public string $brandName = '';

    public string $brandSlug = '';

    public string $loginTitle = '';

    public string $defaultFontFamily = 'Poppins';

    public string $logoPrimaryUrl = '';

    public string $faviconUrl = '';

    public string $defaultPrimaryColor = '#123B66';

    public string $defaultSecondaryColor = '#F59E0B';

    public string $defaultSurfaceColor = '#F8FAFC';

    public string $defaultAccentColor = '#0F766E';

    public string $defaultTextColor = '#0F172A';

    public int $selectedProfileId = 0;

    public string $versionLabel = '';

    public string $themePrimaryColor = '#123B66';

    public string $themeSecondaryColor = '#F59E0B';

    public string $themeSurfaceColor = '#F8FAFC';

    public string $themeAccentColor = '#0F766E';

    public string $themeTextColor = '#0F172A';

    public string $templateName = 'default';

    public bool $showPlatformBrand = true;

    public string $publicationEnvironment = 'staging';

    public string $rollbackReason = '';

    public ?string $operationMessage = null;

    public function registerProfile(AdvancedWhiteLabelBrandService $advancedWhiteLabelBrandService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-white-label');

        $validated = $this->validate([
            'selectedTenantId' => ['required', 'integer', 'min:1'],
            'brandName' => ['required', 'string', 'max:120'],
            'brandSlug' => ['required', 'string', 'max:120'],
            'loginTitle' => ['nullable', 'string', 'max:150'],
            'defaultFontFamily' => ['required', 'string', 'max:120'],
            'logoPrimaryUrl' => ['nullable', 'url', 'max:2048'],
            'faviconUrl' => ['nullable', 'url', 'max:2048'],
            'defaultPrimaryColor' => ['required', 'string', 'size:7'],
            'defaultSecondaryColor' => ['required', 'string', 'size:7'],
            'defaultSurfaceColor' => ['required', 'string', 'size:7'],
            'defaultAccentColor' => ['required', 'string', 'size:7'],
            'defaultTextColor' => ['required', 'string', 'size:7'],
        ]);

        $profile = $advancedWhiteLabelBrandService->registerProfile([
            'cliente_id' => $validated['selectedTenantId'],
            'brand_name' => $validated['brandName'],
            'brand_slug' => $validated['brandSlug'],
            'login_title' => $validated['loginTitle'],
            'default_font_family' => $validated['defaultFontFamily'],
            'default_theme_tokens' => [
                'primary' => $validated['defaultPrimaryColor'],
                'secondary' => $validated['defaultSecondaryColor'],
                'surface' => $validated['defaultSurfaceColor'],
                'accent' => $validated['defaultAccentColor'],
                'text' => $validated['defaultTextColor'],
            ],
        ], [
            'logo_primary' => $validated['logoPrimaryUrl'] ?: null,
            'favicon' => $validated['faviconUrl'] ?: null,
        ]);

        $this->selectedProfileId = $profile->id;
        $this->operationMessage = sprintf('Perfil visual %s registrado.', $profile->brand_name);
    }

    public function registerThemeVersion(AdvancedWhiteLabelBrandService $advancedWhiteLabelBrandService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-white-label');

        $validated = $this->validate([
            'selectedProfileId' => ['required', 'integer', 'min:1'],
            'versionLabel' => ['required', 'string', 'max:80'],
            'themePrimaryColor' => ['required', 'string', 'size:7'],
            'themeSecondaryColor' => ['required', 'string', 'size:7'],
            'themeSurfaceColor' => ['required', 'string', 'size:7'],
            'themeAccentColor' => ['required', 'string', 'size:7'],
            'themeTextColor' => ['required', 'string', 'size:7'],
            'templateName' => ['required', 'string', 'max:80'],
            'showPlatformBrand' => ['required', 'boolean'],
        ]);

        $profile = BrandIdentityProfile::query()->findOrFail($validated['selectedProfileId']);
        $themeVersion = $advancedWhiteLabelBrandService->registerThemeVersion($profile, [
            'version_label' => $validated['versionLabel'],
            'theme_tokens' => [
                'primary' => $validated['themePrimaryColor'],
                'secondary' => $validated['themeSecondaryColor'],
                'surface' => $validated['themeSurfaceColor'],
                'accent' => $validated['themeAccentColor'],
                'text' => $validated['themeTextColor'],
            ],
            'navigation_preferences' => [
                'template_name' => $validated['templateName'],
                'show_platform_brand' => $validated['showPlatformBrand'],
            ],
        ]);

        $this->operationMessage = sprintf('Versao %s registrada em draft.', $themeVersion->version_label);
    }

    public function publishTheme(int $themeVersionId, AdvancedWhiteLabelPublicationService $advancedWhiteLabelPublicationService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-white-label');

        $themeVersion = TenantThemeVersion::query()->findOrFail($themeVersionId);
        $publication = $advancedWhiteLabelPublicationService->publish(
            $themeVersion,
            $this->publicationEnvironment,
            auth('platform')->id()
        );

        $this->operationMessage = $publication->validation_passed
            ? sprintf('Tema %s publicado com sucesso.', $themeVersion->version_label)
            : sprintf('Publicacao bloqueada para %s.', $themeVersion->version_label);
    }

    public function rollbackTheme(int $themeVersionId, AdvancedWhiteLabelRollbackService $advancedWhiteLabelRollbackService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-white-label');

        $validated = $this->validate([
            'rollbackReason' => ['required', 'string', 'max:255'],
        ]);

        $themeVersion = TenantThemeVersion::query()->findOrFail($themeVersionId);
        $advancedWhiteLabelRollbackService->rollback(
            $themeVersion,
            $validated['rollbackReason'],
            auth('platform')->id()
        );

        $this->operationMessage = sprintf('Rollback do tema %s registrado.', $themeVersion->version_label);
    }

    public function render(AdvancedWhiteLabelInspectionService $advancedWhiteLabelInspectionService): View
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-white-label');

        $inspection = $advancedWhiteLabelInspectionService->inspect([
            'tenant_id' => $this->tenantIdFilter > 0 ? $this->tenantIdFilter : null,
            'status' => $this->profileStatusFilter,
            'publication_status' => $this->publicationStatusFilter,
        ]);

        return view('livewire.admin.advanced-white-label-dashboard', [
            'summary' => $inspection['summary'],
            'profiles' => $inspection['profiles'],
            'themes' => $inspection['themes'],
            'publications' => $inspection['publications'],
            'rollbacks' => $inspection['rollbacks'],
        ]);
    }
}
