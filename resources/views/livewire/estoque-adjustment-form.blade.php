<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">Movimentar estoque</h3>
        <p class="mt-1 text-sm text-slate-500">Registre entradas, saídas e ajustes com rastreabilidade em auditoria.</p>
    </div>

    <form wire:submit="salvar" class="space-y-5">
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
                <label class="block text-sm font-semibold text-slate-700">Deposito</label>
                <select wire:model.live="depositoId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @foreach($depositos as $deposito)
                        <option value="{{ $deposito->id }}">{{ $deposito->nome }}</option>
                    @endforeach
                </select>
                @error('depositoId') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Operacao</label>
                <select wire:model.live="tipoOperacao" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="entrada">Entrada</option>
                    <option value="saida">Saida</option>
                    <option value="ajuste_positivo">Ajuste positivo</option>
                    <option value="ajuste_negativo">Ajuste negativo</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Quantidade</label>
                <input type="number" min="1" wire:model.live="quantidade" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('quantidade') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Origem</label>
                <input type="text" wire:model.live="origem" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Justificativa</label>
                <textarea wire:model.live="justificativa" rows="3" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-500">
                Salvar movimentacao
            </button>
        </div>
    </form>
</div>
