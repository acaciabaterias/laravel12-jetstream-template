<div class="space-y-8">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Recovery Automation Governance</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Publicacao controlada de automacao</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Registre uma politica draft, anexe experimento e publique a versao com guardrails, fallback e holdout auditaveis.</p>
    </div>

    @if ($operationMessage)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $operationMessage }}
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-5">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ativas</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ $summary['active_policies'] ?? 0 }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Rollback</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ $summary['rolled_back_policies'] ?? 0 }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Violacoes abertas</p>
            <p class="mt-3 text-3xl font-bold text-slate-900">{{ $summary['open_violations'] ?? 0 }}</p>
        </article>
        <article class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600">Criticas</p>
            <p class="mt-3 text-3xl font-bold text-rose-700">{{ $summary['critical_violations'] ?? 0 }}</p>
        </article>
        <article class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Jornadas afetadas</p>
            <p class="mt-3 text-3xl font-bold text-amber-800">{{ $summary['affected_journeys'] ?? 0 }}</p>
        </article>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status da policy</label>
                <select wire:model.live="policyStatusFilter" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="">Todos</option>
                    <option value="active">Active</option>
                    <option value="superseded">Superseded</option>
                    <option value="rolled_back">Rolled back</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Severidade</label>
                <select wire:model.live="severityFilter" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    <option value="">Todas</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Motivo do rollback</label>
                <input type="text" wire:model="rollbackReason" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="Obrigatorio para rollback">
            </div>
        </div>
    </section>

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.05fr),minmax(0,0.95fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Nova policy draft</h2>

            <form wire:submit="saveDraft" class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Slug</label>
                    <input type="text" wire:model="slug" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nome</label>
                    <input type="text" wire:model="name" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Descricao</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"></textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Severidade alvo</label>
                    <select wire:model="severityScope" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Canal primario</label>
                    <input type="text" wire:model="primaryChannel" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Atraso minimo</label>
                    <input type="number" wire:model="minimumOverdueDays" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Atraso maximo</label>
                    <input type="number" wire:model="maximumOverdueDays" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Max dispatch/dia</label>
                    <input type="number" wire:model="maxDispatchesPerDay" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Cooldown (h)</label>
                    <input type="number" wire:model="cooldownHours" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Fallbacks</label>
                    <input type="text" wire:model="fallbackChannels" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800">
                        Salvar draft
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Publicacao e experimento</h2>

            <form wire:submit="publishPolicy" class="mt-6 grid gap-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Policy draft</label>
                    <select wire:model="selectedPolicyVersionId" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        <option value="">Selecione</option>
                        @foreach ($policies as $policy)
                            <option value="{{ $policy['id'] }}">{{ $policy['name'] }} · {{ strtoupper($policy['status']) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Experimento</label>
                    <input type="text" wire:model="experimentName" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="Opcional">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Holdout ratio</label>
                    <input type="number" step="0.01" wire:model="controlRatio" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Variant A</label>
                        <input type="text" wire:model="variantAChannel" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Variant B</label>
                        <input type="text" wire:model="variantBChannel" class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    </div>
                </div>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" wire:model="enableHoldout" class="rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                    Habilitar holdout
                </label>
                <div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-500">
                        Publicar policy
                    </button>
                </div>
            </form>

            <div class="mt-8 space-y-4">
                @foreach ($policies as $policy)
                    <article class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $policy['name'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $policy['slug'] }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $policy['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ strtoupper($policy['status']) }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">
                            Experimentos: {{ $policy['experiments_count'] }} · Violacoes: {{ $policy['violations_count'] }}
                        </p>
                        @if (($policy['status'] ?? '') === 'active')
                            <div class="mt-4">
                                <button
                                    type="button"
                                    wire:click="rollbackPolicy({{ $policy['id'] }})"
                                    class="inline-flex items-center justify-center rounded-2xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-500"
                                >
                                    Executar rollback
                                </button>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-8 xl:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Violacoes materiais</h2>
            <div class="mt-5 space-y-4">
                @forelse ($violations as $violation)
                    <article class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $violation['violation_type'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $violation['summary'] }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $violation['severity'] === 'critical' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ strtoupper($violation['severity']) }}
                            </span>
                        </div>
                        <p class="mt-3 text-xs text-slate-500">Policy #{{ $violation['policy_version_id'] }} · Jornada #{{ $violation['journey_id'] ?? 'n/a' }}</p>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">Nenhuma violacao encontrada para os filtros atuais.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Contexto de rollback</h2>
            <div class="mt-5 space-y-4">
                @forelse ($rollbackContexts as $context)
                    <article class="rounded-2xl border border-slate-200 p-4">
                        <h3 class="font-semibold text-slate-900">Policy #{{ $context['policy_version_id'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ json_encode($context['rollback']) }}</p>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">Nenhum rollback registrado ate o momento.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
