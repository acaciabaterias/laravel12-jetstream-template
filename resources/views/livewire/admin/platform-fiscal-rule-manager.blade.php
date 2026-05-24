<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Fiscal CFOP governance</h2>
        <p class="mt-1 text-sm text-slate-600">Consult export and import scenarios with governed fallback before operational emission consumes a new fiscal rule set.</p>

        @if ($operationMessage)
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ $operationMessage }}
            </div>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Active publication</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['release_key'] ?? 'fallback only' }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Required scenarios</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['required_scenarios'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Covered scenarios</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['covered_scenarios'] }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">Open issues</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['open_issues'] }}</div>
        </div>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Scenario consultation</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <select wire:model.live="scenarioFilter" class="rounded-lg border-slate-300 text-sm">
                @foreach ($scenarios as $scenario)
                    <option value="{{ $scenario['scenario_key'] }}">
                        {{ $scenario['display_name'] }} ({{ $scenario['operation_direction'] }})
                    </option>
                @endforeach
            </select>

            <select wire:model.live="severityFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Issue severity</option>
                <option value="warning">warning</option>
                <option value="critical">critical</option>
            </select>
        </div>

        @if ($lookup)
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Scenario</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $lookup['display_name'] }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $lookup['operation_direction'] }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm text-slate-500">Resolution type</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $lookup['resolution_type'] }}</div>
                    <div class="mt-1 text-sm text-slate-600">CFOP {{ $lookup['cfop_code'] ?? 'not available' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="text-sm text-slate-500">Classification</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $lookup['classification_code'] ?? 'not informed' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $lookup['cfop_description'] ?? 'Governed fallback without catalog description.' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="text-sm text-slate-500">Validation flags</div>
                    <div class="mt-2 text-sm text-slate-700">{{ implode(', ', $lookup['validation_flags']) }}</div>
                    @if ($lookup['issue'])
                        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                            {{ $lookup['issue']['message'] }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Publish fiscal bundle</h3>
        <p class="mt-1 text-sm text-slate-600">Maintain the central CFOP catalog and required scenario mappings before promoting a new active bundle.</p>

        <div class="mt-4 space-y-4">
            @foreach ($catalogEntries as $index => $catalogEntry)
                <div class="grid gap-3 md:grid-cols-3">
                    <input wire:model.live="catalogEntries.{{ $index }}.cfop_code" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="CFOP code">
                    <input wire:model.live="catalogEntries.{{ $index }}.description" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Description">
                    <select wire:model.live="catalogEntries.{{ $index }}.operation_direction" class="rounded-lg border-slate-300 text-sm">
                        @foreach ($supportedDirections as $supportedDirection)
                            <option value="{{ $supportedDirection }}">{{ $supportedDirection }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        <div class="mt-6 space-y-4">
            @foreach ($scenarioMappings as $index => $scenarioMapping)
                <div class="space-y-3 rounded-xl border border-slate-200 p-4">
                    <div class="grid gap-3 md:grid-cols-5">
                        <input wire:model.live="scenarioMappings.{{ $index }}.scenario_key" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Scenario key">
                        <input wire:model.live="scenarioMappings.{{ $index }}.cfop_code" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="CFOP">
                        <input wire:model.live="scenarioMappings.{{ $index }}.classification_code" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Classification">
                        <select wire:model.live="scenarioMappings.{{ $index }}.operation_direction" class="rounded-lg border-slate-300 text-sm">
                            @foreach ($supportedDirections as $supportedDirection)
                                <option value="{{ $supportedDirection }}">{{ $supportedDirection }}</option>
                            @endforeach
                        </select>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                            {{ implode(', ', (array) $scenarioMapping['validation_flags']) }}
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-4">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.ncm_code" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="NCM">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.tax_regime" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Tax regime">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.cst_code" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="CST">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.csosn_code" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="CSOSN">
                    </div>

                    <div class="grid gap-3 md:grid-cols-5">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.partner_type" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Partner type">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.operation_purpose" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Operation purpose">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.origin_state" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Origin state">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.destination_state" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Destination state">
                        <input wire:model.live="scenarioMappings.{{ $index }}.tax_profile.interstate_tax_rate" type="text" class="rounded-lg border-slate-300 text-sm" placeholder="Interstate rate">
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                        Tax payload keys: {{ implode(', ', array_keys((array) ($scenarioMapping['tax_profile']['tax_payload'] ?? []))) ?: 'none' }}
                    </div>
                </div>
            @endforeach
        </div>

        @if (auth('platform')->user()?->hasRole(['super_admin', 'billing']) && auth('platform')->user()?->ativo)
            <button wire:click="publishRules" type="button" class="mt-4 rounded-lg bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white">
                Publish fiscal bundle
            </button>
        @endif
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Publication history</h3>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <select wire:model.live="statusFilter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Publication status</option>
                <option value="draft">draft</option>
                <option value="active">active</option>
                <option value="superseded">superseded</option>
                <option value="rolled_back">rolled_back</option>
            </select>
            <input wire:model.live="rollbackReason" type="text" placeholder="Rollback reason" class="rounded-lg border-slate-300 text-sm">
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                Active publication: {{ $activePublication?->release_key ?? 'none' }}
            </div>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Release key</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Coverage</th>
                        <th class="pb-2 pr-4">Open issues</th>
                        <th class="pb-2 pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($publications as $publication)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $publication->release_key }}</td>
                            <td class="py-2 pr-4">{{ $publication->status->value }}</td>
                            <td class="py-2 pr-4">{{ $publication->coverage_snapshot['coverage_ratio'] ?? 0 }}</td>
                            <td class="py-2 pr-4">{{ $publication->metadata['open_issues'] ?? 0 }}</td>
                            <td class="py-2 pr-4">
                                @if (auth('platform')->user()?->isSuperAdmin() && auth('platform')->user()?->ativo && $publication->status->value === 'active')
                                    <button wire:click="rollbackPublication({{ $publication->id }})" type="button" class="text-sm font-semibold text-rose-600">
                                        Rollback fiscal publication
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
        <h3 class="text-base font-semibold text-slate-900">Recent fiscal issues</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Scenario</th>
                        <th class="pb-2 pr-4">Issue type</th>
                        <th class="pb-2 pr-4">Severity</th>
                        <th class="pb-2 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($issueReports as $issueReport)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">{{ $issueReport->scenario_key }}</td>
                            <td class="py-2 pr-4">{{ $issueReport->issue_type }}</td>
                            <td class="py-2 pr-4">{{ $issueReport->severity->value }}</td>
                            <td class="py-2 pr-4">{{ $issueReport->resolution_status->value }}</td>
                        </tr>
                    @empty
                        <tr class="border-t border-slate-100">
                            <td colspan="4" class="py-4 text-slate-500">No fiscal issues recorded for the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
