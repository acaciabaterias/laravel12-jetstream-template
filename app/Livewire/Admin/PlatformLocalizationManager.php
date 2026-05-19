<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Http\Requests\Admin\StorePlatformLocalePublicationRequest;
use App\Http\Requests\Admin\UpdatePlatformLocalePreferenceRequest;
use App\Models\PlatformLocalePublicationRecord;
use App\Services\Platform\PlatformLocaleInspectionService;
use App\Services\Platform\PlatformLocalePreferenceService;
use App\Services\Platform\PlatformLocalePublicationService;
use App\Services\Platform\PlatformLocaleRollbackService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class PlatformLocalizationManager extends Component
{
    #[Url(as: 'locale')]
    public string $localeFilter = '';

    #[Url(as: 'severity')]
    public string $severityFilter = '';

    public string $userLocale = 'pt_BR';

    /**
     * @var array<int, string>
     */
    public array $selectedLocales = ['pt_BR', 'en', 'es'];

    public string $defaultLocale = 'pt_BR';

    public string $fallbackLocale = 'en';

    public string $rollbackReason = '';

    public ?string $operationMessage = null;

    public function mount(): void
    {
        $this->userLocale = auth('platform')->user()?->preferred_locale
            ?? config('platform_localization.default_locale', 'pt_BR');
    }

    public function savePreference(PlatformLocalePreferenceService $platformLocalePreferenceService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('use-platform-localization');

        $validated = $this->validate([
            'userLocale' => (new UpdatePlatformLocalePreferenceRequest)->rules()['userLocale'],
        ]);

        $platformLocalePreferenceService->updatePreference(
            auth('platform')->user(),
            $validated['userLocale'],
            request()->session(),
        );

        $this->operationMessage = __('Save language preference');
    }

    public function publishLocales(PlatformLocalePublicationService $platformLocalePublicationService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-localization');

        $request = new StorePlatformLocalePublicationRequest;
        $validated = $this->validate([
            'selectedLocales' => $request->rules()['selectedLocales'],
            'selectedLocales.*' => $request->rules()['selectedLocales.*'],
            'defaultLocale' => $request->rules()['defaultLocale'],
            'fallbackLocale' => $request->rules()['fallbackLocale'],
        ], $request->messages());

        $platformLocalePublicationService->publish(
            $validated['selectedLocales'],
            $validated['defaultLocale'],
            $validated['fallbackLocale'],
            auth('platform')->id(),
        );

        $this->operationMessage = __('Publish locale bundle');
    }

    public function rollbackPublication(int $publicationId, PlatformLocaleRollbackService $platformLocaleRollbackService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('rollback-platform-localization');

        $validated = $this->validate([
            'rollbackReason' => ['required', 'string', 'max:255'],
        ]);

        $publication = PlatformLocalePublicationRecord::query()->findOrFail($publicationId);

        $platformLocaleRollbackService->rollback(
            $publication,
            $validated['rollbackReason'],
            auth('platform')->id(),
        );

        $this->operationMessage = __('Rollback locale publication');
    }

    public function render(PlatformLocaleInspectionService $platformLocaleInspectionService): View
    {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-localization');

        $inspection = $platformLocaleInspectionService->inspect([
            'locale' => $this->localeFilter,
            'severity' => $this->severityFilter,
            'limit' => 25,
            'publication_limit' => 10,
        ]);

        return view('livewire.admin.platform-localization-manager', [
            'summary' => $inspection['summary'],
            'coverage' => $inspection['coverage'],
            'publications' => $inspection['publications'],
            'missingKeyReports' => $inspection['missing_key_reports'],
            'supportedLocales' => (array) config('platform_localization.supported_locales', []),
        ]);
    }
}
