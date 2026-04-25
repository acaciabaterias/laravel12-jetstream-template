@php
    $itemTotalPreview = $previewPrecoFinal !== null ? (float) $previewPrecoFinal * (int) $quantidade : null;
    $step = ! $valeId ? 1 : (($vale && $vale->itens->isNotEmpty()) ? 3 : 2);
    $stepLabels = [1 => 'Cliente', 2 => 'Itens', 3 => 'Pagamento'];
@endphp

<div class="rounded-lg border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/60 lg:p-6">
    <div class="flex flex-col gap-5 border-b border-slate-200 pb-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="inline-flex rounded-lg border border-[rgba(18,59,102,0.14)] bg-[rgba(18,59,102,0.06)] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#123b66]">
                Operação comercial
            </div>
            <h3 class="mt-4 text-2xl font-semibold tracking-tight text-slate-950">Novo vale com fluxo guiado</h3>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Cadastre o cliente, monte os itens e acompanhe o Net Price com sucata em tempo real antes de faturar.</p>
        </div>

        <div class="grid gap-2 sm:grid-cols-3">
            @foreach ($stepLabels as $index => $label)
                <div class="rounded-lg border px-4 py-3 {{ $step >= $index ? 'border-[#123b66]/15 bg-[#123b66] text-white' : 'border-slate-200 bg-slate-50 text-slate-500' }}">
                    <div class="flex items-center gap-3">
                        <span class="flex size-7 items-center justify-center rounded-full text-xs font-bold {{ $step >= $index ? 'bg-white text-[#123b66]' : 'bg-white text-slate-400' }}">{{ $index }}</span>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] {{ $step >= $index ? 'text-white/70' : 'text-slate-400' }}">Step {{ $index }}</p>
                            <p class="text-sm font-semibold">{{ $label }}</p>
                        </div>
                    </div>
                    <div class="mt-3 h-1 rounded-full {{ $step >= $index ? 'bg-[#f59e0b]' : 'bg-slate-200' }}"></div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.12fr_0.88fr]">
        <div class="space-y-6">
            <form wire:submit="createVale" class="rounded-lg border border-slate-200 bg-slate-50/70 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#123b66]">Step 1</p>
                        <h4 class="mt-2 text-lg font-semibold text-slate-950">Cliente e contexto do atendimento</h4>
                    </div>
                    @if ($vale)
                        <span class="rounded-lg border border-[#123b66]/15 bg-white px-3 py-1 text-xs font-semibold text-[#123b66]">Vale #{{ $vale->id }}</span>
                    @endif
                </div>

                <div class="mt-5 grid gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Cliente</label>
                        <select wire:model.live="clienteId" class="mt-2 block w-full rounded-lg border-slate-200 bg-white shadow-sm focus:border-[#123b66] focus:ring-[#123b66]" @disabled($valeId)>
                            <option value="">Selecione...</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->razao_social }}</option>
                            @endforeach
                        </select>
                        @error('clienteId') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Observações do vale</label>
                        <textarea wire:model.live="observacoes" rows="3" class="mt-2 block w-full rounded-lg border-slate-200 bg-white shadow-sm focus:border-[#123b66] focus:ring-[#123b66]" @disabled($valeId) placeholder="Contexto do atendimento, troca, urgência, coleta de sucata..."></textarea>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#123b66] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#123b66]/20 transition hover:bg-[#0f3358] disabled:cursor-not-allowed disabled:opacity-60" @disabled($valeId)>
                        {{ $valeId ? 'Vale carregado' : 'Salvar cliente e abrir vale' }}
                    </button>
                </div>
            </form>

            <form wire:submit="addItem" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm shadow-slate-100">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#123b66]">Step 2</p>
                        <h4 class="mt-2 text-lg font-semibold text-slate-950">Itens e busca dinâmica de baterias</h4>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-700">Net Price unitário</span>
                        <span class="mt-1 block text-lg font-semibold text-slate-950">
                            {{ $previewPrecoFinal !== null ? 'R$ '.number_format($previewPrecoFinal, 2, ',', '.') : 'Aguardando seleção' }}
                        </span>
                    </div>
                </div>

                <div class="mt-5">
                    <label class="block text-sm font-semibold text-slate-700">Buscar bateria</label>
                    <div class="mt-2 flex gap-3">
                        <input type="text" wire:model.live.debounce.300ms="buscaBateria" class="block w-full rounded-lg border-slate-200 bg-slate-50 shadow-sm focus:border-[#123b66] focus:ring-[#123b66]" placeholder="SKU, marca ou referência">
                    </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse ($baterias as $bateria)
                        <button
                            type="button"
                            wire:click="selectBateria({{ $bateria->id }})"
                            class="rounded-lg border px-4 py-4 text-left transition {{ $bateriaId === $bateria->id ? 'border-[#123b66] bg-[#123b66] text-white shadow-lg shadow-[#123b66]/20' : 'border-slate-200 bg-slate-50 hover:border-[#123b66]/30 hover:bg-white' }}"
                        >
                            <p class="text-sm font-semibold">{{ $bateria->sku }}</p>
                            <p class="mt-1 text-sm {{ $bateriaId === $bateria->id ? 'text-white/75' : 'text-slate-500' }}">{{ $bateria->marca }}</p>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] {{ $bateriaId === $bateria->id ? 'text-[#f59e0b]' : 'text-[#123b66]' }}">
                                Adicionar bateria
                            </p>
                        </button>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-sm text-slate-500 md:col-span-2">
                            Nenhuma bateria encontrada para esse filtro.
                        </div>
                    @endforelse
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-[0.7fr_0.3fr]">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Bateria selecionada</label>
                            <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                @if ($selectedBateria)
                                    {{ $selectedBateria->sku }} · {{ $selectedBateria->marca }}
                                @else
                                    Selecione uma bateria acima
                                @endif
                            </div>
                            @error('bateriaId') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Quantidade</label>
                            <input type="number" min="1" wire:model.live="quantidade" class="mt-2 block w-full rounded-lg border-slate-200 bg-slate-50 shadow-sm focus:border-[#123b66] focus:ring-[#123b66]">
                            @error('quantidade') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Estimativa do item</p>
                        <p class="mt-3 text-2xl font-semibold text-slate-950">
                            {{ $itemTotalPreview !== null ? 'R$ '.number_format($itemTotalPreview, 2, ',', '.') : '--' }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">Preço final com a política de sucata aplicada em tempo real.</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Preço base</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $previewPrecoOriginal !== null ? 'R$ '.number_format($previewPrecoOriginal, 2, ',', '.') : '--' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Acréscimo sucata</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $previewAcrescimoSucata !== null ? 'R$ '.number_format($previewAcrescimoSucata, 2, ',', '.') : '--' }}</p>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-700">
                        <input type="checkbox" wire:model.live="devolveuSucata" class="rounded border-slate-300 text-[#123b66] focus:ring-[#123b66]">
                        Cliente devolveu sucata no atendimento
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-slate-700">Observação do item</label>
                    <textarea wire:model.live="observacaoItem" rows="2" class="mt-2 block w-full rounded-lg border-slate-200 bg-slate-50 shadow-sm focus:border-[#123b66] focus:ring-[#123b66]" placeholder="Ex.: troca imediata, coleta pendente, entrega expressa..."></textarea>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#f59e0b] px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-[#f59e0b]/20 transition hover:bg-[#e69008]">
                        Adicionar bateria ao vale
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-[linear-gradient(180deg,#123b66_0%,#0f2f50_100%)] p-5 text-white shadow-xl shadow-[#123b66]/20">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/60">Step 3</p>
                <h4 class="mt-2 text-xl font-semibold">Pagamento e fechamento do atendimento</h4>
                <p class="mt-2 text-sm leading-6 text-white/70">O faturamento acontece pela lista ao lado, mas aqui você acompanha o total e deixa o vale pronto para cobrar.</p>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-white/10 bg-white/5 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/50">Cliente</p>
                        <p class="mt-2 text-sm font-semibold">{{ $vale?->cliente?->razao_social ?? 'Aguardando seleção' }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/50">Status</p>
                        <p class="mt-2 text-sm font-semibold">{{ $vale ? ucfirst($vale->status) : 'Rascunho' }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-lg bg-white px-5 py-5 text-slate-950">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Total do vale</p>
                    <p class="mt-3 text-4xl font-semibold">{{ $vale ? 'R$ '.number_format($vale->valor_total, 2, ',', '.') : 'R$ 0,00' }}</p>
                    <p class="mt-3 text-sm text-slate-500">Depois de revisar os itens, use as ações da lista para visualizar, cancelar ou faturar.</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-5">
                <h4 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Resumo dos itens</h4>

                @if ($vale)
                    <div class="mt-4 space-y-3">
                        @forelse ($vale->itens as $item)
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-950">{{ $item->bateria->sku }} · {{ $item->bateria->marca }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Qtd: {{ $item->quantidade }} · {{ $item->flag_devolveu_sucata ? 'Com sucata' : 'Sem sucata' }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-[#123b66]">R$ {{ number_format((float) $item->preco_unitario_final, 2, ',', '.') }}</p>
                                </div>
                                @if ($item->observacao)
                                    <p class="mt-3 text-sm text-slate-500">{{ $item->observacao }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="rounded-lg border border-dashed border-slate-200 bg-white px-4 py-8 text-sm text-slate-500">Nenhum item adicionado ainda.</p>
                        @endforelse
                    </div>
                @else
                    <p class="mt-4 rounded-lg border border-dashed border-slate-200 bg-white px-4 py-8 text-sm text-slate-500">Crie ou selecione um vale para começar a montar o atendimento.</p>
                @endif
            </div>
        </div>
    </div>
</div>
