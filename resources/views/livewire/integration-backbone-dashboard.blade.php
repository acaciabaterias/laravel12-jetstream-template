<div class="rounded-lg border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/60 lg:p-6">
    <div class="mb-6 flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="inline-flex rounded-lg border border-[#123b66]/15 bg-[#123b66]/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#123b66]">
                Backbone de integracao
            </div>
            <h3 class="mt-4 text-2xl font-semibold tracking-tight text-slate-950">Operacao de eventos e gateway</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Inspecione eventos pendentes, dead-letter, contratos versionados e entregas recentes.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900">
                Pendentes: <span class="font-semibold">{{ $pendingEvents }}</span>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Dead-letter: <span class="font-semibold">{{ $deadLetters }}</span>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                Contratos: <span class="font-semibold">{{ $contractsCount }}</span>
            </div>
            <div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                Latencia media: <span class="font-semibold">{{ number_format(collect($metrics['latency'] ?? [])->flatten()->avg() ?? 0, 1, ',', '.') }} ms</span>
            </div>
        </div>
    </div>

    @if ($operationMessage)
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ $operationMessage }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="text-sm text-slate-600">
                    Tipo de evento
                    <input wire:model.live="eventTypeFilter" type="text" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#123b66] focus:outline-none focus:ring-2 focus:ring-[#123b66]/15" placeholder="VALE_FATURADO">
                </label>
                <label class="text-sm text-slate-600">
                    Status da entrega
                    <select wire:model.live="statusFilter" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#123b66] focus:outline-none focus:ring-2 focus:ring-[#123b66]/15">
                        <option value="">Todos</option>
                        <option value="pending">pending</option>
                        <option value="processed">processed</option>
                        <option value="failed">failed</option>
                        <option value="dead_letter">dead_letter</option>
                        <option value="replayed">replayed</option>
                        <option value="skipped">skipped</option>
                    </select>
                </label>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-4">
                <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Outbox recente</h4>
                <div class="mt-4 space-y-3">
                    @forelse ($outboxes as $outbox)
                        <div class="rounded-lg border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-950">{{ $outbox->event_type }}</p>
                                <span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $outbox->status->value === 'dead_letter' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $outbox->status->value }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">Tenant: {{ $outbox->tenant_external_ref }}</p>
                            <p class="mt-1 text-xs text-slate-400">Idempotencia: {{ $outbox->idempotency_key }}</p>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-200 bg-white px-4 py-8 text-sm text-slate-500">Nenhum evento na outbox.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-4">
            <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Entregas recentes</h4>
            <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Direcao</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Destino</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tentativa</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Acao</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($deliveries as $delivery)
                            <tr>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $delivery->direction->value }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $delivery->target }}</td>
                                <td class="px-4 py-4 text-sm">
                                    <span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $delivery->status->value === 'processed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $delivery->status->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right text-sm font-semibold text-slate-900">{{ $delivery->attempt_number }}</td>
                                <td class="px-4 py-4 text-right text-sm">
                                    @if (in_array($delivery->status->value, ['failed', 'dead_letter'], true))
                                        <button
                                            type="button"
                                            wire:click="replayDelivery({{ $delivery->id }})"
                                            class="rounded-lg border border-[#123b66]/15 bg-[#123b66]/5 px-3 py-2 text-xs font-semibold text-[#123b66] transition hover:border-[#123b66]/25 hover:bg-[#123b66]/10"
                                        >
                                            Replay
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Nenhuma entrega registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
