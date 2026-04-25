@php
    $whiteLabel = \App\Models\WhiteLabelConfig::query()->first();
    $normalizeHex = function (?string $color, string $fallback): string {
        if (! is_string($color) || ! preg_match('/^#?[0-9a-fA-F]{6}$/', $color)) {
            return $fallback;
        }

        return '#'.ltrim($color, '#');
    };
    $toRgb = fn (string $hex): string => implode(' ', sscanf(ltrim($hex, '#'), '%02x%02x%02x'));
    $brandPrimary = $normalizeHex($whiteLabel?->cor_primaria, '#123b66');
    $brandSecondary = $normalizeHex($whiteLabel?->cor_secundaria, '#f59e0b');
    $brandBackground = $normalizeHex($whiteLabel?->cor_fundo, '#f8fafc');
    $brandTitle = $whiteLabel?->titulo_login ?: config('app.name', 'BateriaExpert');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-seo::meta />

        <title>{{ $brandTitle }} - BateriaExpert</title>

        <link rel="icon" href="{{ $whiteLabel?->favicon_url ?? asset('favicon.ico') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <style>
            :root {
                --brand-primary: {{ $brandPrimary }};
                --brand-primary-rgb: {{ $toRgb($brandPrimary) }};
                --brand-secondary: {{ $brandSecondary }};
                --brand-secondary-rgb: {{ $toRgb($brandSecondary) }};
                --brand-surface: {{ $brandBackground }};
            }

            {!! $whiteLabel?->custom_css !!}
        </style>
    </head>
    <body class="h-full font-sans antialiased text-slate-900">
        <x-banner />

        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            <div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,rgba(var(--brand-primary-rgb),0.14),transparent_32%),radial-gradient(circle_at_top_right,rgba(var(--brand-secondary-rgb),0.14),transparent_24%),linear-gradient(180deg,#f8fafc_0%,#edf3f8_100%)]"></div>

            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/55 md:hidden" @click="sidebarOpen = false"></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[88vw] transform flex-col border-r border-white/20 bg-slate-950 text-white shadow-2xl transition duration-300 md:w-72 md:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            >
                <div class="border-b border-white/10 px-6 py-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-4">
                        @if ($whiteLabel?->logo_url)
                            <img src="{{ $whiteLabel->logo_url }}" alt="Logo da empresa" class="h-12 w-auto rounded-2xl bg-white/90 p-2 shadow-lg">
                        @else
                            <div class="brand-logo-mark flex h-12 w-12 items-center justify-center rounded-2xl shadow-brand">
                                <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2L7 13H12L10 22L17 11H12L14 2Z" fill="currentColor"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <p class="font-display text-xl font-bold tracking-tight">{{ $brandTitle }}</p>
                            <p class="text-sm text-slate-300">ERP para revendas de baterias</p>
                        </div>
                    </a>
                </div>

                <div class="px-4 py-6">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Operação do Dia</p>
                        <p class="mt-3 font-display text-2xl font-semibold">Painel Comercial</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Controle vendas, entregas, garantias e financeiro no mesmo fluxo operacional.</p>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 px-4 pb-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-white text-slate-950 shadow-lg' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-[rgba(var(--brand-secondary-rgb),0.16)] text-[var(--brand-secondary)]">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3l7 6v8a1 1 0 01-1 1h-4v-5H8v5H4a1 1 0 01-1-1V9l7-6z"/></svg>
                        </span>
                        Dashboard
                    </a>

                    @if (Route::has('profile.show'))
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-white/10 text-white">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 10a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 1114 0H3z"/></svg>
                            </span>
                            Perfil e Conta
                        </a>
                    @endif

                    @if (Route::has('admin.dashboard') && auth()->user()?->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-white/10 text-white">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h6v6H3V3zm8 0h6v6h-6V3zM3 11h6v6H3v-6zm8 2h6v2h-6v-2z"/></svg>
                            </span>
                            Plataforma Central
                        </a>
                    @endif
                </nav>

                <div class="mt-auto border-t border-white/10 px-4 py-5">
                    <div class="flex items-center gap-3 rounded-3xl bg-white/5 p-4">
                        <img class="h-12 w-12 rounded-2xl object-cover ring-2 ring-white/10" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs uppercase tracking-[0.18em] text-slate-400">{{ str(auth()->user()->papel ?? 'usuario')->replace('_', ' ') }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="md:pl-72">
                <header class="sticky top-0 z-30 border-b border-white/60 bg-white/80 backdrop-blur-xl">
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm md:hidden" @click="sidebarOpen = true">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3 5h14v2H3V5zm0 4h14v2H3V9zm0 4h14v2H3v-2z"/></svg>
                            </button>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">BateriaExpert</p>
                                <p class="font-display text-xl font-semibold text-slate-950">Painel Operacional</p>
                            </div>
                        </div>

                        <div class="hidden items-center gap-3 md:flex">
                            <div class="brand-pill border-slate-200 bg-white text-slate-600">
                                White label ativo
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-2xl bg-[var(--brand-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-brand transition hover:opacity-95">
                                    Sair
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                @if (isset($header))
                    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
                        <div class="brand-shell px-6 py-6">
                            {{ $header }}
                        </div>
                    </section>
                @endif

                <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>

                @if ($whiteLabel?->mostrar_marca_plataforma ?? true)
                    <footer class="mx-auto max-w-7xl px-4 pb-10 pt-2 sm:px-6 lg:px-8">
                        <div class="brand-shell flex flex-col gap-3 px-6 py-5 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                            <p><span class="font-semibold text-slate-900">BateriaExpert ERP</span> • Gestão de vendas, sucata, logística e garantias.</p>
                            <p>Suporte: suporte@bateriaexpert.com • (11) 99999-9999</p>
                        </div>
                    </footer>
                @endif
            </div>
        </div>

        @stack('modals')
        @livewireScripts
        @stack('scripts')

        @if ($whiteLabel?->custom_js)
            <script>{!! $whiteLabel->custom_js !!}</script>
        @endif
    </body>
</html>
