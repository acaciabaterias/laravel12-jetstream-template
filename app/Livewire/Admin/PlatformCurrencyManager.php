<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Http\Requests\Admin\StorePlatformCurrencyPublicationRequest;
use App\Http\Requests\Admin\UpdatePlatformCurrencyPreferenceRequest;
use App\Models\PlatformCurrencyPublicationRecord;
use App\Services\Platform\PlatformCurrencyFormattingService;
use App\Services\Platform\PlatformCurrencyInspectionService;
use App\Services\Platform\PlatformCurrencyPreferenceService;
use App\Services\Platform\PlatformCurrencyPublicationService;
use App\Services\Platform\PlatformCurrencyRollbackService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class PlatformCurrencyManager extends Component
{
    #[Url(as: 'currency')]
    public string $currencyFilter = '';

    #[Url(as: 'severity')]
    public string $severityFilter = '';

    public string $userCurrency = 'BRL';

    /**
     * @var array<int, string>
     */
    public array $selectedCurrencies = ['BRL', 'USD', 'EUR'];

    public string $baseCurrency = 'BRL';

    public string $defaultCurrency = 'BRL';

    /**
     * @var array<string, float|int|string>
     */
    public array $exchangeRates = [
        'USD' => '5.42',
        'EUR' => '5.93',
    ];

    public string $rollbackReason = '';

    public ?string $operationMessage = null;

    public function mount(): void
    {
        $this->userCurrency = auth('platform')->user()?->preferred_currency
            ?? config('platform_currencies.default_currency', 'BRL');
    }

    public function savePreference(PlatformCurrencyPreferenceService $platformCurrencyPreferenceService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('use-platform-currencies');

        $validated = $this->validate([
            'userCurrency' => (new UpdatePlatformCurrencyPreferenceRequest)->rules()['userCurrency'],
        ]);

        $platformCurrencyPreferenceService->updatePreference(
            auth('platform')->user(),
            $validated['userCurrency'],
            request()->session(),
        );

        $this->operationMessage = 'Save currency preference';
    }

    public function publishCurrencies(PlatformCurrencyPublicationService $platformCurrencyPublicationService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-currencies');

        $request = new StorePlatformCurrencyPublicationRequest;
        $validated = $this->validate([
            'selectedCurrencies' => $request->rules()['selectedCurrencies'],
            'selectedCurrencies.*' => $request->rules()['selectedCurrencies.*'],
            'baseCurrency' => $request->rules()['baseCurrency'],
            'defaultCurrency' => $request->rules()['defaultCurrency'],
            'exchangeRates' => $request->rules()['exchangeRates'],
        ], $request->messages());

        $platformCurrencyPublicationService->publish(
            $validated['selectedCurrencies'],
            $validated['baseCurrency'],
            $validated['defaultCurrency'],
            $validated['exchangeRates'],
            auth('platform')->id(),
        );

        $this->operationMessage = 'Publish currency bundle';
    }

    public function rollbackPublication(int $publicationId, PlatformCurrencyRollbackService $platformCurrencyRollbackService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('rollback-platform-currencies');

        $validated = $this->validate([
            'rollbackReason' => ['required', 'string', 'max:255'],
        ]);

        $publication = PlatformCurrencyPublicationRecord::query()->findOrFail($publicationId);

        $platformCurrencyRollbackService->rollback(
            $publication,
            $validated['rollbackReason'],
            auth('platform')->id(),
        );

        $this->operationMessage = 'Rollback currency publication';
    }

    public function render(
        PlatformCurrencyPreferenceService $platformCurrencyPreferenceService,
        PlatformCurrencyFormattingService $platformCurrencyFormattingService,
        PlatformCurrencyPublicationService $platformCurrencyPublicationService,
        PlatformCurrencyInspectionService $platformCurrencyInspectionService,
    ): View {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-currencies');

        $inspection = $platformCurrencyInspectionService->inspect([
            'currency' => $this->currencyFilter,
            'severity' => $this->severityFilter,
            'limit' => 25,
            'publication_limit' => 10,
        ]);
        $activePublication = $platformCurrencyPublicationService->activePublication();
        $resolvedCurrency = config('platform_currencies.current_currency', $this->userCurrency);

        return view('livewire.admin.platform-currency-manager', [
            'supportedCurrencies' => (array) config('platform_currencies.supported_currencies', []),
            'availableCurrencies' => $platformCurrencyPreferenceService->supportedCurrencies(),
            'resolvedCurrency' => $resolvedCurrency,
            'publication' => $activePublication,
            'summary' => $inspection['summary'],
            'issueReports' => $inspection['issues'],
            'currencyPreview' => [
                [
                    'label' => 'MRR preview',
                    'formatted' => $platformCurrencyFormattingService->formatFromBase(31200, $resolvedCurrency),
                ],
                [
                    'label' => 'Monthly billing preview',
                    'formatted' => $platformCurrencyFormattingService->formatFromBase(24700, $resolvedCurrency),
                ],
                [
                    'label' => 'Recovery exposure preview',
                    'formatted' => $platformCurrencyFormattingService->formatFromBase(8450, $resolvedCurrency),
                ],
            ],
            'publications' => $inspection['publications'],
        ]);
    }
}
