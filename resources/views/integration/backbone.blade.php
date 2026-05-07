<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">Integration Backbone</p>
            <h2 class="mt-2 font-display text-3xl font-bold tracking-tight text-slate-950">Inspecao operacional</h2>
        </div>
    </x-slot>

    <div class="space-y-6">
        <livewire:integration-backbone-dashboard />
    </div>
</x-app-layout>
