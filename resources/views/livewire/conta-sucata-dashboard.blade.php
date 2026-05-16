<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-end justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Conta sucata</h3>
            <p class="mt-1 text-sm text-slate-500">Acompanhe o saldo financeiro acumulado da sucata por movimentacao.</p>
        </div>
        <div class="rounded-2xl bg-amber-100 px-4 py-3 text-right">
            <p class="text-xs uppercase tracking-[0.16em] text-amber-700">Saldo atual</p>
            <p class="mt-1 text-xl font-semibold text-amber-950">R$ {{ number_format($saldoAtual, 2, ',', '.') }}</p>
        </div>
    </div>

    <form wire:submit="registrarMovimento" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Tipo de entidade</label>
                <select wire:model.live="entidadeTipo" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="bateria">Bateria</option>
                    <option value="cliente">Cliente</option>
                    <option value="fornecedor">Fornecedor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Entidade</label>
                <select wire:model.live="entidadeId" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione...</option>
                    @if($entidadeTipo === 'bateria')
                        @foreach($baterias as $bateria)
                            <option value="{{ $bateria->id }}">{{ $bateria->sku }} · {{ $bateria->marca }}</option>
                        @endforeach
                    @elseif($entidadeTipo === 'cliente')
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                        @endforeach
                    @else
                        @foreach($fornecedores as $fornecedor)
                            <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                        @endforeach
                    @endif
                </select>
                @error('entidadeId') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Tipo</label>
                <select wire:model.live="tipoMovimento" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="credito">Credito</option>
                    <option value="debito">Debito</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Quantidade (Kg)</label>
                <input type="number" min="0.01" step="0.01" wire:model.live="quantidadeKg" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('quantidadeKg') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Valor unitario</label>
                <input type="number" min="0" step="0.01" wire:model.live="valorUnitario" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('valorUnitario') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Origem</label>
                <input type="text" wire:model.live="origem" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-2xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-400">
                Registrar movimento
            </button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Entidade</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Origem</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($movimentacoes as $movimentacao)
                    <tr>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ class_basename($movimentacao->entidade_tipo) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ ucfirst($movimentacao->tipo_movimento) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $movimentacao->origem }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">
                            R$ {{ number_format((float) $movimentacao->saldo_resultante, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Nenhum movimento de sucata registrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
