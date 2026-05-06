<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-slate-900">{{ $userId ? 'Editar usuário' : 'Novo usuário' }}</h3>
    <form wire:submit="save" class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-slate-700">Nome</label>
            <input type="text" wire:model.live="name" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-700">Email</label>
            <input type="email" wire:model.live="email" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-700">{{ $userId ? 'Nova senha (opcional)' : 'Senha' }}</label>
            <input type="password" wire:model.live="password" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-700">Papel</label>
            <select wire:model.live="papel" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione...</option>
                @foreach($availableRoles as $roleKey => $roleLabel)
                    <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                @endforeach
            </select>
            @error('papel') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model.live="ativo" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Usuário ativo
            </label>
            @error('ativo') <span class="block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Salvar
            </button>
        </div>
    </form>
</div>
