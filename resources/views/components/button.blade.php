<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-2xl bg-[var(--brand-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white shadow-brand transition duration-150 ease-in-out hover:-translate-y-0.5 hover:opacity-95 focus:outline-hidden focus:ring-2 focus:ring-[var(--brand-primary)] focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
