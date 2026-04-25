<x-guest-layout>
    <div class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,rgba(var(--brand-primary-rgb),0.18),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(var(--brand-secondary-rgb),0.16),transparent_28%),linear-gradient(160deg,#f8fafc_0%,#e9f1f8_100%)]"></div>

        <div class="grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-2xl shadow-slate-900/10 backdrop-blur xl:grid-cols-[1.1fr,0.9fr]">
            <section class="relative hidden overflow-hidden bg-slate-950 px-10 py-12 text-white xl:flex xl:flex-col xl:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.18),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(18,59,102,0.55),transparent_48%)]"></div>

                <div class="relative">
                    <div class="flex items-center gap-4">
                        <div class="brand-logo-mark flex h-14 w-14 items-center justify-center rounded-3xl shadow-brand">
                            <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2L7 13H12L10 22L17 11H12L14 2Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-display text-2xl font-bold tracking-tight">BateriaExpert</p>
                            <p class="text-sm text-slate-300">ERP especializado em baterias automotivas</p>
                        </div>
                    </div>

                    <div class="mt-16 max-w-lg">
                        <p class="brand-pill border-white/15 bg-white/10 text-slate-200">Operação comercial + logística reversa</p>
                        <h1 class="mt-6 font-display text-5xl font-bold leading-tight">Venda, entrega, garantia e financeiro no mesmo painel.</h1>
                        <p class="mt-6 text-lg leading-8 text-slate-300">Centralize a rotina da revenda com métricas operacionais, controle de sucata, ordens de serviço e gestão por tenant.</p>
                    </div>
                </div>

                <div class="relative grid gap-4 sm:grid-cols-3">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm text-slate-400">Vendas</p>
                        <p class="mt-2 font-display text-2xl font-semibold">+24%</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm text-slate-400">Entregas</p>
                        <p class="mt-2 font-display text-2xl font-semibold">97%</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm text-slate-400">Garantias</p>
                        <p class="mt-2 font-display text-2xl font-semibold">12 OS</p>
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
                        <p class="mt-4 font-display text-3xl font-bold text-slate-950">BateriaExpert</p>
                        <p class="mt-2 text-sm text-slate-500">Acesse sua operação com segurança.</p>
                    </div>

                    <div class="mt-6 xl:mt-0">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--brand-primary)]">Entrar no ERP</p>
                        <h2 class="mt-3 font-display text-4xl font-bold tracking-tight text-slate-950">Login da plataforma</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-500">Use seu e-mail corporativo para acessar vendas, estoque, logística e garantia.</p>
                    </div>

                    <x-validation-errors class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" />

                    @session('status')
                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ $value }}
                        </div>
                    @endsession

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                            <x-input id="email" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <label for="password" class="text-sm font-semibold text-slate-700">{{ __('Password') }}</label>
                                @if (Route::has('password.request'))
                                    <a class="text-sm font-medium text-[var(--brand-primary)] transition hover:opacity-80" href="{{ route('password.request') }}">
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif
                            </div>
                            <x-input id="password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-[var(--brand-primary)] focus:ring-[var(--brand-primary)]" type="password" name="password" required autocomplete="current-password" />
                        </div>

                        <label for="remember_me" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <x-checkbox id="remember_me" name="remember" class="rounded border-slate-300 text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]" />
                            <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
                        </label>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[var(--brand-primary)] px-5 py-3 text-sm font-semibold text-white shadow-brand transition hover:-translate-y-0.5 hover:opacity-95">
                            {{ __('Log in') }}
                        </button>
                    </form>

                    @if (Route::has('admin.login'))
                        <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                            <p class="font-semibold text-slate-900">Precisa acessar o backoffice central?</p>
                            <a href="{{ route('admin.login') }}" class="mt-2 inline-flex items-center font-semibold text-[var(--brand-primary)] transition hover:opacity-80">
                                Ir para o login administrativo
                            </a>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>
