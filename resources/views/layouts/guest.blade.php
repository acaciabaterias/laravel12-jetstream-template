@php
    $resolvedCliente = request()->attributes->get('cliente');
    $whiteLabelModel = new \App\Models\WhiteLabelConfig();
    $canFilterByCliente = \Illuminate\Support\Facades\Schema::connection($whiteLabelModel->getConnectionName() ?? config('database.default'))
        ->hasColumn($whiteLabelModel->getTable(), 'cliente_id');
    $whiteLabel = \App\Models\WhiteLabelConfig::query()
        ->when($canFilterByCliente && $resolvedCliente?->id, fn ($query) => $query->where('cliente_id', $resolvedCliente->id))
        ->first()
        ?? \App\Models\WhiteLabelConfig::query()->first();
    $normalizeHex = function (?string $color, string $fallback): string {
        if (! is_string($color) || ! preg_match('/^#?[0-9a-fA-F]{6}$/', $color)) {
            return $fallback;
        }

        return '#'.ltrim($color, '#');
    };
    $toRgb = fn (string $hex): string => implode(' ', sscanf(ltrim($hex, '#'), '%02x%02x%02x'));
    $brandPrimary = $normalizeHex($whiteLabel?->cor_primaria, '#123b66');
    $brandSecondary = $normalizeHex($whiteLabel?->cor_secundaria, '#f59e0b');
    $brandSurface = $normalizeHex($whiteLabel?->cor_fundo, '#f8fafc');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $whiteLabel?->titulo_login ?: config('app.name', 'BateriaExpert') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full font-sans text-slate-900 antialiased">
        <div
            class="min-h-screen"
            style="
                --brand-primary: {{ $brandPrimary }};
                --brand-primary-rgb: {{ $toRgb($brandPrimary) }};
                --brand-secondary: {{ $brandSecondary }};
                --brand-secondary-rgb: {{ $toRgb($brandSecondary) }};
                --brand-surface: {{ $brandSurface }};
            "
        >
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
