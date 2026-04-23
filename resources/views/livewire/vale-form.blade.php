<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Criacao de vales</h3>
            <p class="mt-1 text-sm text-slate-500">Monte o atendimento comercial, reserve estoque e acompanhe o net price com sucata.</p>
        </div>
        @if($vale)
            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">Vale #{{ $vale->id }}</span>
        @endif
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-5">
            <form wire:submit="createVale" class="space-y-4 rounded-2xl border border-slate-200 p-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Cliente</label>
                    <select wire:model.live="clienteId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($valeId)>
                        <option value="">Selecione...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->razao_social }}</option>
                        @endforeach
                    </select>
                    @error('clienteId') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Observacoes</label>
                    <textarea wire:model.live="observacoes" rows="3" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($valeId)></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500" @disabled($valeId)>
                        {{ $valeId ? 'Vale criado' : 'Criar vale' }}
                    </button>
                </div>
            </form>

            <form wire:submit="addItem" class="space-y-4 rounded-2xl border border-slate-200 p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Bateria</label>
                        <select wire:model.live="bateriaId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach($baterias as $bateria)
                                <option value="{{ $bateria->id }}">{{ $bateria->sku }} · {{ $bateria->marca }}</option>
                            @endforeach
                        </select>
                        @error('bateriaId') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Quantidade</label>
                        <input type="number" min="1" wire:model.live="quantidade" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('quantidade') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <label class="flex items-center gap-3 text-sm text-slate-700">
                        <input type="checkbox" wire:model.live="devolveuSucata" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Cliente devolveu sucata
                    </label>
                    @if($previewPrecoFinal !== null)
                        <p class="mt-3 text-sm font-semibold text-slate-800">Preco unitario previsto: R$ {{ number_format($previewPrecoFinal, 2, ',', '.') }}</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Observacao do item</label>
                    <textarea wire:model.live="observacaoItem" rows="2" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-500">
                        Adicionar item
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Resumo do vale</h4>

            @if($vale)
                <div class="mt-4 space-y-3">
                    <p class="text-sm text-slate-600">Cliente: <span class="font-medium text-slate-900">{{ $vale->cliente->razao_social }}</span></p>
                    <p class="text-sm text-slate-600">Status: <span class="font-medium text-slate-900">{{ ucfirst($vale->status) }}</span></p>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($vale->itens as $item)
                        <div class="rounded-2xl bg-white p-4 shadow-sm">
                            <p class="font-medium text-slate-900">{{ $item->bateria->sku }} · {{ $item->bateria->marca }}</p>
                            <p class="mt-1 text-sm text-slate-600">Qtd: {{ $item->quantidade }} · Final: R$ {{ number_format((float) $item->preco_unitario_final, 2, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $item->flag_devolveu_sucata ? 'Com devolucao de sucata' : 'Sem devolucao de sucata' }}</p>
                        </div>
                    @empty
                        <p class="mt-4 text-sm text-slate-500">Nenhum item adicionado ainda.</p>
                    @endforelse
                </div>

                <div class="mt-6 rounded-2xl bg-slate-950 px-5 py-4 text-white">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-300">Total</p>
                    <p class="mt-2 text-3xl font-semibold">R$ {{ number_format($vale->valor_total, 2, ',', '.') }}</p>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500">Crie um vale para liberar a adicao de itens e reservas.</p>
            @endif
        </div>
    </div>
</div>
