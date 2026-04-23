<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5">
        <h3 class="text-lg font-semibold text-slate-900">Painel financeiro</h3>
        <p class="mt-1 text-sm text-slate-500">Acompanhe contas, transações recentes e pendências de conciliação.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="space-y-3">
            @forelse($contas as $conta)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">{{ $conta->banco }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $conta->agencia }} / {{ $conta->conta }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Nenhuma conta bancária cadastrada.</p>
            @endforelse
            <div class="rounded-2xl bg-amber-100 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-amber-700">Pendências</p>
                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ $transacoesPendentes }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Descrição</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($transacoesRecentes as $transacao)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ ucfirst($transacao->tipo) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $transacao->descricao }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">R$ {{ number_format((float) $transacao->valor, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Sem transações recentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
