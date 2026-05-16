<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Operação de Recuperação de Receita</h2>
        <p class="mt-1 text-sm text-slate-600">Escalone casos críticos e registre promessas de pagamento sem perder a trilha operacional.</p>
    </div>

    @if (session()->has('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="escalateCase" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h3 class="text-base font-semibold text-slate-900">Escalonar Caso</h3>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Caso</label>
                <select wire:model="casoRecuperacaoReceitaId" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Selecione</option>
                    @foreach ($casos as $caso)
                        <option value="{{ $caso->id }}">#{{ $caso->id }} - {{ $caso->cliente->razao_social ?? $caso->cliente->nome_fantasia ?? 'Cliente' }}</option>
                    @endforeach
                </select>
                @error('casoRecuperacaoReceitaId') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Responsável</label>
                <select wire:model="ownerUserId" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Automático</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Motivo</label>
                <input wire:model="escalationReason" type="text" class="w-full rounded-lg border-slate-300 text-sm">
            </div>

            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                Escalonar
            </button>
        </form>

        <form wire:submit="recordPromise" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h3 class="text-base font-semibold text-slate-900">Registrar Promessa</h3>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Caso</label>
                <select wire:model="casoRecuperacaoReceitaId" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">Selecione</option>
                    @foreach ($casos as $caso)
                        <option value="{{ $caso->id }}">#{{ $caso->id }} - {{ $caso->cliente->razao_social ?? $caso->cliente->nome_fantasia ?? 'Cliente' }}</option>
                    @endforeach
                </select>
                @error('casoRecuperacaoReceitaId') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Valor prometido</label>
                <input wire:model="promisedAmount" type="number" step="0.01" class="w-full rounded-lg border-slate-300 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Data prometida</label>
                <input wire:model="promisedDate" type="date" class="w-full rounded-lg border-slate-300 text-sm">
                @error('promisedDate') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Suspender até</label>
                <input wire:model="suspendsUntil" type="date" class="w-full rounded-lg border-slate-300 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Observações</label>
                <textarea wire:model="promiseNotes" class="w-full rounded-lg border-slate-300 text-sm" rows="3"></textarea>
            </div>

            <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white">
                Registrar promessa
            </button>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Casos Ativos</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">Caso</th>
                        <th class="pb-2 pr-4">Cliente</th>
                        <th class="pb-2 pr-4">Estágio</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Responsável</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    @foreach ($casos as $caso)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4">#{{ $caso->id }}</td>
                            <td class="py-2 pr-4">{{ $caso->cliente->razao_social ?? $caso->cliente->nome_fantasia ?? 'Cliente' }}</td>
                            <td class="py-2 pr-4">{{ $caso->current_stage }}</td>
                            <td class="py-2 pr-4">{{ $caso->status->value }}</td>
                            <td class="py-2 pr-4">{{ $caso->owner?->name ?? 'Automático' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
