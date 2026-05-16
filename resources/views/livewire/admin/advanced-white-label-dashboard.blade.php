<div class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-amber-200/70 bg-[linear-gradient(135deg,#1f2937_0%,#0f172a_38%,#d97706_140%)] p-6 text-white shadow-xl">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-200">Module 018</p>
        <div class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <h2 class="font-display text-3xl font-semibold tracking-tight">Advanced white label experience</h2>
                <p class="mt-2 text-sm leading-6 text-slate-200">Centralize identidade visual por tenant, publique temas versionados com validacao minima e reverta branding sem tocar em arquivos soltos do deploy.</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-slate-100">
                Shell atual + governanca versionada + rollback auditavel
            </div>
        </div>
    </div>

    @if ($operationMessage)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ $operationMessage }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Brand profiles</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['profiles'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Active profiles</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-700">{{ $summary['active_profiles'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Draft themes</p>
            <p class="mt-2 text-3xl font-semibold text-amber-600">{{ $summary['draft_themes'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Published themes</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $summary['published_themes'] }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-3">
            <input wire:model.live="tenantIdFilter" type="number" min="0" placeholder="Tenant ID" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
            <select wire:model.live="profileStatusFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                <option value="">Todos os status de tema</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="rolled_back">Rolled back</option>
            </select>
            <select wire:model.live="publicationStatusFilter" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                <option value="">Todas as publicacoes</option>
                <option value="published">Published</option>
                <option value="rejected">Rejected</option>
                <option value="pending">Pending</option>
            </select>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-950">Registrar identidade visual</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <input wire:model="selectedTenantId" type="number" min="1" placeholder="Tenant ID" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="brandName" type="text" placeholder="Brand name" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="brandSlug" type="text" placeholder="brand-slug" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="loginTitle" type="text" placeholder="Titulo de login" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="defaultFontFamily" type="text" placeholder="Tipografia base" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="logoPrimaryUrl" type="url" placeholder="Logo URL" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="faviconUrl" type="url" placeholder="Favicon URL" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400 md:col-span-2" />
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-5">
                <input wire:model="defaultPrimaryColor" type="text" placeholder="#123B66" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="defaultSecondaryColor" type="text" placeholder="#F59E0B" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="defaultSurfaceColor" type="text" placeholder="#F8FAFC" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="defaultAccentColor" type="text" placeholder="#0F766E" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="defaultTextColor" type="text" placeholder="#0F172A" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
            </div>
            <button wire:click="registerProfile" class="mt-5 rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                Registrar identidade
            </button>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-950">Registrar versao de tema</h3>
            <div class="mt-4 grid gap-3">
                <input wire:model="selectedProfileId" type="number" min="1" placeholder="Brand profile ID" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="versionLabel" type="text" placeholder="v1 launch" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="templateName" type="text" placeholder="Template name" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                    <input wire:model="showPlatformBrand" type="checkbox" class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-400" />
                    Mostrar marca da plataforma
                </label>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-5">
                <input wire:model="themePrimaryColor" type="text" placeholder="#123B66" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="themeSecondaryColor" type="text" placeholder="#F59E0B" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="themeSurfaceColor" type="text" placeholder="#F8FAFC" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="themeAccentColor" type="text" placeholder="#0F766E" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
                <input wire:model="themeTextColor" type="text" placeholder="#0F172A" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
            </div>
            <div class="mt-4 flex flex-col gap-3 md:flex-row">
                <select wire:model="publicationEnvironment" class="rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="staging">Staging</option>
                    <option value="production">Production</option>
                </select>
                <button wire:click="registerThemeVersion" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Salvar draft
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-950">Perfis e versoes</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Tenant</th>
                        <th class="pb-2 pr-4">Brand</th>
                        <th class="pb-2 pr-4">Version</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Validation</th>
                        <th class="pb-2 pr-4">Acoes</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @forelse ($themes as $theme)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 pr-4">{{ $theme['tenant_subdomain'] ?? 'n/d' }}</td>
                            <td class="py-3 pr-4">{{ $theme['brand_name'] }}</td>
                            <td class="py-3 pr-4">{{ $theme['version_label'] }}</td>
                            <td class="py-3 pr-4">{{ $theme['status'] }}</td>
                            <td class="py-3 pr-4">
                                @if (($theme['validation_summary']['passed'] ?? null) === false)
                                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">blocked</span>
                                @elseif (($theme['validation_summary']['passed'] ?? null) === true)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">passed</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">pending</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <div class="flex flex-col gap-2 lg:flex-row">
                                    <button wire:click="publishTheme({{ $theme['id'] }})" class="rounded-2xl border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                        Publicar
                                    </button>
                                    @if ($theme['status'] === 'published')
                                        <button wire:click="rollbackTheme({{ $theme['id'] }})" class="rounded-2xl border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                            Rollback
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-slate-500">Nenhuma versao registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <input wire:model="rollbackReason" type="text" placeholder="Motivo do rollback" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" />
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-950">Publicacoes recentes</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">Tenant</th>
                            <th class="pb-2 pr-4">Status</th>
                            <th class="pb-2 pr-4">Ambiente</th>
                            <th class="pb-2 pr-4">Validacao</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @forelse ($publications as $publication)
                            <tr class="border-t border-slate-100">
                                <td class="py-3 pr-4">{{ $publication['tenant_subdomain'] ?? 'n/d' }}</td>
                                <td class="py-3 pr-4">{{ $publication['status'] }}</td>
                                <td class="py-3 pr-4">{{ $publication['environment'] }}</td>
                                <td class="py-3 pr-4">{{ $publication['validation_passed'] ? 'ok' : implode('; ', $publication['validation_messages']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-slate-500">Nenhuma publicacao registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-950">Rollbacks recentes</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">Tenant</th>
                            <th class="pb-2 pr-4">Restaurado</th>
                            <th class="pb-2 pr-4">Operador</th>
                            <th class="pb-2 pr-4">Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @forelse ($rollbacks as $rollback)
                            <tr class="border-t border-slate-100">
                                <td class="py-3 pr-4">{{ $rollback['tenant_subdomain'] ?? 'n/d' }}</td>
                                <td class="py-3 pr-4">{{ $rollback['restored_theme_version_id'] ?? 'fallback' }}</td>
                                <td class="py-3 pr-4">{{ $rollback['operator'] ?? 'n/d' }}</td>
                                <td class="py-3 pr-4">{{ $rollback['reason'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-slate-500">Nenhum rollback registrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
