<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Operacao do entregador</h3>
        <p class="mt-1 text-sm text-slate-500">Atualize paradas, colete sucata, registre recebimentos e sincronize eventos do turno.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Rota</label>
                <select wire:model.live="rotaEntregaId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach($rotas as $rota)
                        <option value="{{ $rota->id }}">Rota #{{ $rota->id }} · {{ $rota->status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Parada</label>
                <select wire:model.live="pontoEntregaId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach($pontos as $ponto)
                        <option value="{{ $ponto->id }}">{{ $ponto->ordem_parada }} · {{ $ponto->cliente->razao_social }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Metodo</label>
                    <select wire:model.live="metodoPagamento" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="pix">Pix</option>
                        <option value="cartao">Cartao</option>
                        <option value="dinheiro">Dinheiro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Valor recebido</label>
                    <input type="number" step="0.01" wire:model.live="valorRecebido" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Peso sucata (Kg)</label>
                    <input type="number" step="0.01" wire:model.live="pesoSucataColetado" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Dispositivo</label>
                    <input type="text" wire:model.live="dispositivoUuid" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Observacao</label>
                <textarea wire:model.live="observacao" rows="3" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
        </div>

        <div class="space-y-3">
            <button type="button" wire:click="registerPayment" class="w-full rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500">
                Registrar recebimento
            </button>
            <button type="button" wire:click="updateStop" class="w-full rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                Atualizar parada
            </button>
            <button type="button" wire:click="registerGeoEvent" class="w-full rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                Registrar evento geo
            </button>
            <button type="button" wire:click="closeRoute" class="w-full rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-400">
                Encerrar rota
            </button>
        </div>
    </div>
</div>
