<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Planejamento de rotas</h3>
        <p class="mt-1 text-sm text-slate-500">Monte rotas de entrega, associe entregador e acrescente as paradas operacionais do dia.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-5">
            <form wire:submit="createRoute" class="space-y-4 rounded-2xl border border-slate-200 p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Entregador</label>
                        <select wire:model.live="entregadorId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach($entregadores as $entregador)
                                <option value="{{ $entregador->id }}">{{ $entregador->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Veiculo</label>
                        <select wire:model.live="veiculoId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sem veiculo</option>
                            @foreach($veiculos as $veiculo)
                                <option value="{{ $veiculo->id }}">{{ $veiculo->modelo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Observacoes</label>
                    <textarea wire:model.live="observacoes" rows="2" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        {{ $rotaEntregaId ? 'Rota criada' : 'Criar rota' }}
                    </button>
                </div>
            </form>

            <form wire:submit="addStop" class="space-y-4 rounded-2xl border border-slate-200 p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Cliente</label>
                        <select wire:model.live="clienteId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Vale</label>
                        <select wire:model.live="valeId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sem vale vinculado</option>
                            @foreach($vales as $vale)
                                <option value="{{ $vale->id }}">Vale #{{ $vale->id }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Endereco de entrega</label>
                    <input type="text" wire:model.live="enderecoEntrega" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
                        Adicionar parada
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Rota atual</h4>
            @if($rota)
                <div class="mt-4 space-y-3">
                    <p class="text-sm text-slate-600">Entregador: <span class="font-medium text-slate-900">{{ $rota->entregador->name }}</span></p>
                    <p class="text-sm text-slate-600">Status: <span class="font-medium text-slate-900">{{ ucfirst($rota->status) }}</span></p>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach($rota->pontos as $ponto)
                        <div class="rounded-2xl bg-white p-4 shadow-sm">
                            <p class="font-medium text-slate-900">{{ $ponto->ordem_parada }}. {{ $ponto->cliente->razao_social }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $ponto->endereco_entrega }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500">Nenhuma rota criada ainda.</p>
            @endif
        </div>
    </div>
</div>
