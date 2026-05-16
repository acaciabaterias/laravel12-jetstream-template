<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>BateriaExpert Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen font-sans text-slate-900 antialiased">
    <div class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,rgba(18,59,102,0.18),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(245,158,11,0.16),transparent_26%),linear-gradient(180deg,#f8fafc_0%,#e9f1f8_100%)]"></div>

        <div class="grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-2xl shadow-slate-900/10 backdrop-blur xl:grid-cols-[1.05fr,0.95fr]">
            <section class="relative hidden overflow-hidden bg-slate-950 px-10 py-12 text-white xl:flex xl:flex-col xl:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.18),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(18,59,102,0.5),transparent_48%)]"></div>

                <div class="relative">
                    <div class="flex items-center gap-4">
                        <div class="brand-logo-mark flex h-14 w-14 items-center justify-center rounded-3xl shadow-brand">
                            <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2L7 13H12L10 22L17 11H12L14 2Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-display text-2xl font-bold tracking-tight">BateriaExpert Admin</p>
                            <p class="text-sm text-slate-300">Backoffice central do SaaS</p>
                        </div>
                    </div>

                    <div class="mt-16 max-w-lg">
                        <p class="brand-pill border-white/15 bg-white/10 text-slate-200">Operação central + suporte</p>
                        <h1 class="mt-6 font-display text-5xl font-bold leading-tight">Gerencie tenants, assinaturas e suporte em um único painel.</h1>
                        <p class="mt-6 text-lg leading-8 text-slate-300">Acesse a visão central do ERP para acompanhar clientes SaaS, white label, provisionamento e administração da plataforma.</p>
                    </div>
                </div>

                <div class="relative grid gap-4 sm:grid-cols-3">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm text-slate-400">Tenants</p>
                        <p class="mt-2 font-display text-2xl font-semibold">Ativos</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm text-slate-400">Suporte</p>
                        <p class="mt-2 font-display text-2xl font-semibold">Central</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm text-slate-400">Financeiro</p>
                        <p class="mt-2 font-display text-2xl font-semibold">MRR</p>
                    </div>
                </div>
            </section>

            <section class="px-6 py-8 sm:px-10 sm:py-10">
                <div class="mx-auto max-w-md">
                    <div class="text-center xl:hidden">
                        <div class="brand-logo-mark mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] shadow-brand">
                            <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2L7 13H12L10 22L17 11H12L14 2Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <p class="mt-4 font-display text-3xl font-bold text-slate-950">BateriaExpert Admin</p>
                        <p class="mt-2 text-sm text-slate-500">Acesso restrito ao time da plataforma.</p>
                    </div>

                    <div class="mt-6 xl:mt-0">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--brand-primary)]">Plataforma central</p>
                        <h2 class="mt-3 font-display text-4xl font-bold tracking-tight text-slate-950">Login administrativo</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-500">Entre com sua conta de administrador da plataforma para acessar o painel central.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="text-sm font-semibold text-slate-700">E-mail</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                        </div>

                        <div>
                            <label for="password" class="text-sm font-semibold text-slate-700">Senha</label>
                            <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                        </div>

                        <label for="remember" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <input id="remember" name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            <span class="text-sm text-slate-600">Manter conectado</span>
                        </label>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[var(--brand-primary)] px-5 py-3 text-sm font-semibold text-white shadow-brand transition hover:-translate-y-0.5 hover:opacity-95">
                            Entrar no painel admin
                        </button>
                    </form>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-900">Vai entrar na operação da loja?</p>
                        <a href="{{ route('login') }}" class="mt-2 inline-flex items-center font-semibold text-[var(--brand-primary)] transition hover:opacity-80">
                            Ir para o login do ERP
                        </a>
                    </div>

                    <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-900">Credencial seeded padrão</p>
                        <p class="mt-1">E-mail: <code class="rounded bg-white px-1.5 py-0.5 text-xs">admin@bateriaexpert.com</code></p>
                        <p class="mt-1">Senha: <code class="rounded bg-white px-1.5 py-0.5 text-xs">12345678</code></p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
