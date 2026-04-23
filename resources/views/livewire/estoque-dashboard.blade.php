<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Saldo por deposito</h3>
            <p class="mt-1 text-sm text-slate-500">Visao consolidada do estoque tenant-aware por bateria e deposito.</p>
        </div>
        <div class="w-full md:max-w-sm">
            <label class="block text-sm font-semibold text-slate-700">Buscar bateria</label>
            <input type="text" wire:model.live="filtroBusca" class="mt-2 block w-full rounded-2xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="SKU ou marca">
        </div>
    </div>

    <div class="mb-4 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-slate-950 px-5 py-4 text-white">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-300">Saldo total</p>
            <p class="mt-3 text-3xl font-semibold">{{ $saldoTotal }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Bateria</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Deposito</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($saldos as $saldo)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $saldo->bateria?->sku }} · {{ $saldo->bateria?->marca }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $saldo->deposito?->nome }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">{{ $saldo->quantidade_atual }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Nenhum saldo registrado ate o momento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
