<nav x-data="{ open: false }" class="border-b border-white/60 bg-white/80 backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-18 justify-between py-3">
            <div class="flex items-center gap-3">
                <div class="shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="brand-logo-mark flex h-11 w-11 items-center justify-center rounded-2xl shadow-brand">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2L7 13H12L10 22L17 11H12L14 2Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="hidden sm:block">
                            <p class="font-display text-lg font-semibold tracking-tight text-slate-950">BateriaExpert</p>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Painel do Tenant</p>
                        </div>
                    </a>
                </div>

                <div class="hidden items-center gap-2 sm:ms-6 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (Route::has('profile.show'))
                        <x-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                            {{ __('Perfil') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden items-center gap-3 sm:flex">
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-2xl">
                                    <button type="button" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:border-[var(--brand-primary)] hover:text-slate-900 focus:outline-hidden focus:bg-slate-50">
                                        {{ Auth::user()->currentTeam->name }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60 space-y-1 rounded-3xl bg-white p-2">
                                    <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-dropdown-link>
                                    @endcan

                                    @if (Auth::user()->allTeams()->count() > 1)
                                        <div class="my-2 border-t border-slate-200"></div>

                                        <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                            {{ __('Switch Teams') }}
                                        </div>

                                        @foreach (Auth::user()->allTeams() as $team)
                                            <x-switchable-team :team="$team" />
                                        @endforeach
                                    @endif
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <div class="relative">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm transition hover:border-[var(--brand-primary)] focus:outline-hidden">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <img class="size-9 rounded-2xl object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                @endif

                                <span class="hidden text-start md:block">
                                    <span class="block text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</span>
                                    <span class="block text-xs uppercase tracking-[0.16em] text-slate-500">{{ str(Auth::user()->papel ?? 'usuario')->replace('_', ' ') }}</span>
                                </span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-56 space-y-1 rounded-3xl bg-white p-2">
                                <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    {{ __('Manage Account') }}
                                </div>

                                <x-dropdown-link href="{{ route('profile.show') }}">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                        {{ __('API Tokens') }}
                                    </x-dropdown-link>
                                @endif

                                <div class="my-2 border-t border-slate-200"></div>

                                <form method="POST" action="{{ route('logout') }}" x-data>
                                    @csrf

                                    <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-hidden">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-200 bg-white/95 sm:hidden">
        <div class="space-y-2 px-4 pt-4 pb-3">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                {{ __('Profile') }}
            </x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 px-4 pt-4 pb-5">
            <div class="flex items-center gap-3">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0">
                        <img class="size-11 rounded-2xl object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="text-base font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-4 space-y-2">
                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="pt-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                        {{ __('Manage Team') }}
                    </div>

                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan

                    @if (Auth::user()->allTeams()->count() > 1)
                        <div class="pt-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ __('Switch Teams') }}
                        </div>

                        @foreach (Auth::user()->allTeams() as $team)
                            <x-switchable-team :team="$team" component="responsive-nav-link" />
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
</nav>
