<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'BateriaExpert') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans text-slate-900 antialiased">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,rgba(18,59,102,0.18),transparent_26%),radial-gradient(circle_at_top_right,rgba(245,158,11,0.14),transparent_22%),linear-gradient(180deg,#f8fafc_0%,#ebf2f8_100%)]"></div>

            <main class="mx-auto flex min-h-screen max-w-7xl flex-col justify-center px-4 py-10 sm:px-6 lg:px-8">
                <section class="brand-shell overflow-hidden">
                    <div class="grid gap-0 xl:grid-cols-[1.15fr_0.85fr]">
                        <div class="relative overflow-hidden bg-slate-950 px-8 py-10 text-white sm:px-12 sm:py-14">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.16),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(18,59,102,0.58),transparent_48%)]"></div>

                            <div class="relative">
                                <div class="flex items-center gap-4">
                                    <div class="brand-logo-mark flex h-14 w-14 items-center justify-center rounded-3xl shadow-brand">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14 2L7 13H12L10 22L17 11H12L14 2Z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-display text-2xl font-bold tracking-tight">BateriaExpert ERP</p>
                                        <p class="text-sm text-slate-300">Gestão especializada para baterias automotivas</p>
                                    </div>
                                </div>

                                <div class="mt-12 max-w-2xl">
                                    <p class="brand-pill border-white/15 bg-white/10 text-slate-200">ERP + logística reversa + SaaS</p>
                                    <h1 class="mt-6 font-display text-5xl font-bold leading-tight sm:text-6xl">Venda, entrega, garantia e gestão central em um único ecossistema.</h1>
                                    <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">Escolha a entrada do sistema: operação do tenant para lojas e filiais, ou painel administrativo central para gestão do SaaS.</p>
                                </div>

                                <div class="mt-10 grid gap-4 sm:grid-cols-3">
                                    <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                                        <p class="text-sm text-slate-400">ERP Core</p>
                                        <p class="mt-2 font-display text-2xl font-semibold">Tenant</p>
                                    </div>
                                    <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                                        <p class="text-sm text-slate-400">Backoffice</p>
                                        <p class="mt-2 font-display text-2xl font-semibold">Admin</p>
                                    </div>
                                    <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                                        <p class="text-sm text-slate-400">Monitoramento</p>
                                        <p class="mt-2 font-display text-2xl font-semibold">Operação</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/95 px-8 py-10 sm:px-10 sm:py-12">
                            <div class="mx-auto max-w-md">
                                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--brand-primary)]">Escolha sua entrada</p>
                                <h2 class="mt-3 font-display text-4xl font-bold tracking-tight text-slate-950">Acesse o ambiente correto</h2>
                                <p class="mt-3 text-sm leading-6 text-slate-500">Use o acesso do tenant para a operação diária das lojas e o acesso admin para o backoffice central do SaaS.</p>

                                <div class="mt-8 space-y-5">
                                    <a href="{{ route('login') }}" class="block rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:border-[var(--brand-primary)] hover:bg-white hover:shadow-lg">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Tenant ERP</p>
                                                <h3 class="mt-2 font-display text-2xl font-semibold text-slate-950">Login operacional</h3>
                                                <p class="mt-2 text-sm leading-6 text-slate-500">Balcão, técnico, estoque, logística, financeiro e dashboard do tenant.</p>
                                            </div>
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[rgba(18,59,102,0.1)] text-[var(--brand-primary)]">
                                                <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3l7 6v8a1 1 0 01-1 1h-4v-5H8v5H4a1 1 0 01-1-1V9l7-6z"/></svg>
                                            </span>
                                        </div>
                                    </a>

                                    <a href="{{ route('admin.login') }}" class="block rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:border-[var(--brand-secondary)] hover:bg-white hover:shadow-lg">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Backoffice SaaS</p>
                                                <h3 class="mt-2 font-display text-2xl font-semibold text-slate-950">Login administrativo</h3>
                                                <p class="mt-2 text-sm leading-6 text-slate-500">Gestão de tenants, filiais, clientes SaaS, assinaturas e suporte central.</p>
                                            </div>
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[rgba(245,158,11,0.16)] text-[var(--brand-secondary)]">
                                                <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h6v6H3V3zm8 0h6v6h-6V3zM3 11h6v6H3v-6zm8 2h6v2h-6v-2z"/></svg>
                                            </span>
                                        </div>
                                    </a>
                                </div>

                                <div class="mt-8 rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-600">
                                    <p class="font-semibold text-slate-900">Acesso rápido</p>
                                    <p class="mt-1">Tenant: <code class="rounded bg-white px-1.5 py-0.5 text-xs">/login</code></p>
                                    <p class="mt-1">Admin: <code class="rounded bg-white px-1.5 py-0.5 text-xs">/admin/login</code></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
