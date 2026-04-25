@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full bg-[rgba(var(--brand-primary-rgb),0.1)] px-4 py-2 text-sm font-semibold leading-5 text-[var(--brand-primary)] transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-full px-4 py-2 text-sm font-medium leading-5 text-slate-500 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-900 focus:outline-hidden focus:bg-slate-100 focus:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
