<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - {{ __('Management Platform') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --brand-primary: #123b66;
            --brand-secondary: #f59e0b;
        }

        [x-cloak] { display: none !important; }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="h-full overflow-hidden font-sans antialiased text-gray-900">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-50">
        <aside class="hidden md:flex md:flex-shrink-0">
            <div class="flex w-64 flex-col bg-slate-900">
                <div class="flex flex-grow flex-col overflow-y-auto pt-5 pb-4">
                    <div class="mb-8 flex items-center px-6">
                        <div class="brand-logo-mark flex h-10 w-10 items-center justify-center rounded-2xl">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2L7 13H12L10 22L17 11H12L14 2Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="ml-3 font-display text-xl font-bold tracking-tight text-white">Bateria<span class="text-[var(--brand-secondary)]">Expert Admin</span></span>
                    </div>
                    <nav class="flex-1 space-y-1 px-3">
                        <a href="{{ route('admin.dashboard') }}" class="group flex items-center rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-[var(--brand-primary)] text-white shadow-brand' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ __('Dashboard') }}
                        </a>

                        <div class="px-4 pt-4 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ __('Central Management') }}
                        </div>

                        <a href="{{ route('admin.filiais.index') }}" class="group flex items-center rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.filiais.*') ? 'bg-[var(--brand-primary)] text-white shadow-brand' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0 text-slate-400 group-hover:text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ __('Branches') }}
                        </a>

                        <a href="{{ route('admin.clientes.index') }}" class="group flex items-center rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.clientes.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0 text-slate-400 group-hover:text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ __('Customers / Subscribers') }}
                        </a>
                        <a href="{{ route('admin.localization.index') }}" class="group flex items-center rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.localization.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0 text-slate-400 group-hover:text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 5.5A17.962 17.962 0 0015 12c.613-1.13 1.078-2.35 1.38-3.626M20 19l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2" />
                            </svg>
                            {{ __('Platform internationalization') }}
                        </a>
                    </nav>
                </div>
                <div class="flex flex-shrink-0 bg-slate-800 p-4">
                    <div class="flex items-center">
                        <div>
                            <img class="inline-block h-9 w-9 rounded-full ring-2 ring-slate-700" src="https://ui-avatars.com/api/?name={{ urlencode(auth('platform')->user()->name ?? auth('platform')->user()->nome ?? 'Admin') }}&color=7F9CF5&background=EBF4FF" alt="">
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">{{ auth('platform')->user()->name ?? auth('platform')->user()->nome ?? 'Admin' }}</p>
                            <p class="text-xs font-medium text-slate-400">{{ auth('platform')->user()->papel ?? 'platform' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="relative z-10 flex h-16 flex-shrink-0 border-b border-gray-200 bg-white shadow-sm">
                <button @click="sidebarOpen = true" class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--brand-primary)] md:hidden">
                    <span class="sr-only">Abrir sidebar</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex flex-1 justify-between px-4">
                    <div class="flex flex-1 items-center gap-4">
                        <h1 class="font-display text-lg font-semibold text-gray-800">{{ __('Management Platform') }}</h1>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            {{ app()->getLocale() }}
                        </span>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <div class="ml-3 relative">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:ring-offset-2">
                                    <span class="sr-only">{{ __('Logout') }}</span>
                                    <svg class="h-6 w-6 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="relative flex-1 overflow-y-auto bg-gray-50/50 focus:outline-none">
                <div class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
                        @if (isset($header))
                            <div class="mb-8">
                                {{ $header }}
                            </div>
                        @endif

                        <div class="animate-fade-in">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @stack('modals')
    @livewireScripts
</body>
</html>
