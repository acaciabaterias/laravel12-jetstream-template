<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Platform currencies</h2>
        <p class="mt-1 text-sm text-slate-600">Resolve a display currency per operator while preserving the base values in BRL.</p>

        @if ($operationMessage)
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ $operationMessage }}
            </div>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Resolved currency</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $resolvedCurrency }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Base currency</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['base_currency_code'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Default currency</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['default_currency_code'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Open issues</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['open_issues'] }}</div>
        </div>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Preferred display currency</h3>
        <div class="mt-4 flex gap-3">
            <select wire:model.live="userCurrency" class="rounded-lg border-slate-300 text-sm">
                @foreach ($availableCurrencies as $currencyCode)
                    <option value="{{ $currencyCode }}">
                        {{ $currencyCode }} - {{ $supportedCurrencies[$currencyCode]['label'] ?? $currencyCode }}
                    </option>
                @endforeach
            </select>

            <button wire:click="savePreference" type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                Save currency preference
            </button>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Central value preview</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @foreach ($currencyPreview as $preview)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">{{ $preview['label'] }}</div>
                    <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $preview['formatted'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Publish currency bundle</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            @foreach ($supportedCurrencies as $currencyCode => $definition)
                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">
                    <input wire:model.live="selectedCurrencies" type="checkbox" value="{{ $currencyCode }}" class="rounded border-slate-300">
                    <span>{{ $currencyCode }} - {{ $definition['label'] }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <select wire:model.live="baseCurrency" class="rounded-lg border-slate-300 text-sm">
                @foreach ($supportedCurrencies as $currencyCode => $definition)
                    <option value="{{ $currencyCode }}">Base currency: {{ $currencyCode }}</option>
                @endforeach
            </select>
            <select wire:model.live="defaultCurrency" class="rounded-lg border-slate-300 text-sm">
                @foreach ($supportedCurrencies as $currencyCode => $definition)
                    <option value="{{ $currencyCode }}">Default currency: {{ $currencyCode }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @foreach ($supportedCurrencies as $currencyCode => $definition)
                @continue($currencyCode === $baseCurrency)
                <label class="text-sm text-slate-700">
                    <span class="mb-2 block font-medium">{{ $currencyCode }} rate against {{ $baseCurrency }}</span>
                    <input wire:model.live="exchangeRates.{{ $currencyCode }}" type="number" step="0.00000001" class="w-full rounded-lg border-slate-300 text-sm">
                </label>
            @endforeach
        </div>
        @if (auth('platform')->user()?->hasRole(['super_admin', 'billing']) && auth('platform')->user()?->ativo)
            <button wire:click="publishCurrencies" type="button" class="mt-4 rounded-lg bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white">
                Publish currency bundle
            </button>
        @endif
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Publication history</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <input wire:model.live.debounce.300ms="currencyFilter" type="text" placeholder="currency" class="rounded-lg border-slate-300 text-sm">
            <select wire:model.live="severityFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Issue severity</option>
                <option value="warning">warning</option>
                <option value="critical">critical</option>
            </select>
            <input wire:model.live="rollbackReason" type="text" placeholder="Rollback currency publication" class="rounded-lg border-slate-300 text-sm">
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Release key</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Base currency</th>
                        <th class="pb-2 pr-4">Default currency</th>
                        <th class="pb-2 pr-4">Supported currencies</th>
                        <th class="pb-2 pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($publications as $currencyPublication)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $currencyPublication->release_key }}</td>
                            <td class="py-2 pr-4">{{ $currencyPublication->status->value }}</td>
                            <td class="py-2 pr-4">{{ $currencyPublication->base_currency_code }}</td>
                            <td class="py-2 pr-4">{{ $currencyPublication->default_currency_code }}</td>
                            <td class="py-2 pr-4">{{ implode(', ', (array) $currencyPublication->supported_currencies) }}</td>
                            <td class="py-2 pr-4">
                                @if (auth('platform')->user()?->isSuperAdmin() && auth('platform')->user()?->ativo && $currencyPublication->status->value === 'active')
                                    <button wire:click="rollbackPublication({{ $currencyPublication->id }})" type="button" class="text-sm font-semibold text-rose-600">
                                        Rollback currency publication
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
        <h3 class="text-base font-semibold text-slate-900">Open conversion issues</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Currency</th>
                        <th class="pb-2 pr-4">Issue type</th>
                        <th class="pb-2 pr-4">Severity</th>
                        <th class="pb-2 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($issueReports as $issueReport)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $issueReport->currency_code }}</td>
                            <td class="py-2 pr-4">{{ $issueReport->issue_type }}</td>
                            <td class="py-2 pr-4">{{ $issueReport->severity->value }}</td>
                            <td class="py-2 pr-4">{{ $issueReport->resolution_status->value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
