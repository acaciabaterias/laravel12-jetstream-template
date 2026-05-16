<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700 shadow-xs transition duration-150 ease-in-out hover:-translate-y-0.5 hover:border-[var(--brand-primary)] hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-[var(--brand-primary)] focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
