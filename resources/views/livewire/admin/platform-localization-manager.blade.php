<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Platform internationalization') }}</h2>
        <p class="mt-1 text-sm text-slate-600">{{ __('Enter with your platform administrator account to access the central panel.') }}</p>

        @if ($operationMessage)
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ $operationMessage }}
            </div>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Active publication') }}</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">#{{ $summary['active_publication_id'] ?? '-' }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Default locale') }}</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['default_locale'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Fallback locale') }}</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['fallback_locale'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Open missing keys') }}</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['open_missing_keys'] }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Preferred language') }}</h3>
            <div class="mt-4 flex gap-3">
                <select wire:model.live="userLocale" class="rounded-lg border-slate-300 text-sm">
                    @foreach ($supportedLocales as $locale => $definition)
                        <option value="{{ $locale }}">{{ $definition['label'] }}</option>
                    @endforeach
                </select>

                <button wire:click="savePreference" type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                    {{ __('Save language preference') }}
                </button>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">{{ __('Publish locale bundle') }}</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                @foreach ($supportedLocales as $locale => $definition)
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">
                        <input wire:model.live="selectedLocales" type="checkbox" value="{{ $locale }}" class="rounded border-slate-300">
                        <span>{{ $definition['native'] }}</span>
                    </label>
                @endforeach
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <select wire:model.live="defaultLocale" class="rounded-lg border-slate-300 text-sm">
                    @foreach ($supportedLocales as $locale => $definition)
                        <option value="{{ $locale }}">{{ __('Default locale') }}: {{ $definition['label'] }}</option>
                    @endforeach
                </select>
                <select wire:model.live="fallbackLocale" class="rounded-lg border-slate-300 text-sm">
                    @foreach ($supportedLocales as $locale => $definition)
                        <option value="{{ $locale }}">{{ __('Fallback locale') }}: {{ $definition['label'] }}</option>
                    @endforeach
                </select>
            </div>
            @if (auth('platform')->user()?->hasRole(['super_admin', 'support']) && auth('platform')->user()?->ativo)
                <button wire:click="publishLocales" type="button" class="mt-4 rounded-lg bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white">
                    {{ __('Publish locale bundle') }}
                </button>
            @endif
        </section>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">{{ __('Coverage by locale') }}</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Locale</th>
                        <th class="pb-2 pr-4">{{ __('Supported locales') }}</th>
                        <th class="pb-2 pr-4">{{ __('Coverage by locale') }}</th>
                        <th class="pb-2 pr-4">{{ __('Missing keys') }}</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($coverage as $coverageLine)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $coverageLine['locale'] }}</td>
                            <td class="py-2 pr-4">{{ $coverageLine['translated_keys'] }}/{{ $coverageLine['required_keys'] }}</td>
                            <td class="py-2 pr-4">{{ number_format(((float) $coverageLine['coverage_ratio']) * 100, 1) }}%</td>
                            <td class="py-2 pr-4">{{ count($coverageLine['missing_keys']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <input wire:model.live.debounce.300ms="localeFilter" type="text" placeholder="locale" class="rounded-lg border-slate-300 text-sm">
            <select wire:model.live="severityFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">{{ __('Missing keys') }}</option>
                <option value="warning">warning</option>
                <option value="critical">critical</option>
            </select>
            <input wire:model.live="rollbackReason" type="text" placeholder="{{ __('Rollback locale publication') }}" class="rounded-lg border-slate-300 text-sm">
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">{{ __('Release key') }}</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">{{ __('Default locale') }}</th>
                        <th class="pb-2 pr-4">{{ __('Fallback locale') }}</th>
                        <th class="pb-2 pr-4">{{ __('Missing keys') }}</th>
                        <th class="pb-2 pr-4">Ações</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($publications as $publication)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $publication->release_key }}</td>
                            <td class="py-2 pr-4">{{ $publication->status->value }}</td>
                            <td class="py-2 pr-4">{{ $publication->default_locale }}</td>
                            <td class="py-2 pr-4">{{ $publication->fallback_locale }}</td>
                            <td class="py-2 pr-4">{{ count((array) ($publication->coverage_snapshot[$publication->default_locale]['missing_keys'] ?? [])) }}</td>
                            <td class="py-2 pr-4">
                                @if (auth('platform')->user()?->isSuperAdmin() && auth('platform')->user()?->ativo && $publication->status->value === 'active')
                                    <button wire:click="rollbackPublication({{ $publication->id }})" type="button" class="text-sm font-semibold text-rose-600">
                                        {{ __('Rollback locale publication') }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">{{ __('Missing keys') }}</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Locale</th>
                        <th class="pb-2 pr-4">{{ __('Missing keys') }}</th>
                        <th class="pb-2 pr-4">Grupo</th>
                        <th class="pb-2 pr-4">Severity</th>
                        <th class="pb-2 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($missingKeyReports as $report)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $report->locale_code }}</td>
                            <td class="py-2 pr-4">{{ $report->translation_key }}</td>
                            <td class="py-2 pr-4">{{ $report->context_group }}</td>
                            <td class="py-2 pr-4">{{ $report->severity->value }}</td>
                            <td class="py-2 pr-4">{{ $report->resolution_status->value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
