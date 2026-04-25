@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-2xl bg-[rgba(var(--brand-primary-rgb),0.1)] px-4 py-3 text-start text-base font-semibold text-[var(--brand-primary)] transition duration-150 ease-in-out'
            : 'block w-full rounded-2xl px-4 py-3 text-start text-base font-medium text-slate-600 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-900 focus:outline-hidden focus:bg-slate-100 focus:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
