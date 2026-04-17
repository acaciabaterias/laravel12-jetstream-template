@php
    $whiteLabel = \App\Models\WhiteLabelConfig::first();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-seo::meta />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <title>{{ $whiteLabel?->titulo_login ?? config('app.name') }} - BateriaExpert</title>
        
        <link rel="icon" href="{{ $whiteLabel?->favicon_url ?? asset('favicon.ico') }}">
        
        @if($whiteLabel)
        <style>
            :root {
                --primary-color: {{ $whiteLabel->cor_primaria }};
                --secondary-color: {{ $whiteLabel->cor_secundaria }};
                --background-color: {{ $whiteLabel->cor_fundo }};
            }
            {!! $whiteLabel->custom_css !!}
        </style>
        @endif
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        @if($whiteLabel?->logo_url)
            <div class="bg-white p-4 flex justify-center">
                <img src="{{ $whiteLabel->logo_url }}" alt="Logo" class="h-12">
            </div>
        @endif

        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow-sm">
                    <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts

        @if($whiteLabel?->custom_js)
            <script>{!! $whiteLabel->custom_js !!}</script>
        @endif
        
        @if($whiteLabel?->mostrar_marca_plataforma ?? true)
            <footer class="text-center text-xs text-gray-500 p-4">
                Powered by <a href="https://bateriaexpert.com">BateriaExpert</a>
            </footer>
        @endif
    </body>
</html>
