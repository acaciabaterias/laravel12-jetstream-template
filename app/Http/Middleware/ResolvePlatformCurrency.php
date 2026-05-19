<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UsuarioPlataforma;
use App\Services\Platform\PlatformCurrencyFormattingService;
use App\Services\Platform\PlatformCurrencyResolutionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePlatformCurrency
{
    public function __construct(
        private readonly PlatformCurrencyResolutionService $platformCurrencyResolutionService,
        private readonly PlatformCurrencyFormattingService $platformCurrencyFormattingService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var UsuarioPlataforma|null $user */
        $user = auth('platform')->user();
        $currencyCode = $this->platformCurrencyResolutionService->resolve($user, $request->session());
        $context = $this->platformCurrencyFormattingService->contextFor($currencyCode);

        $request->session()->put('platform_currency', $currencyCode);
        $request->attributes->set('platform_currency', $context);
        config([
            'platform_currencies.current_currency' => $currencyCode,
            'platform_currencies.current_context' => $context,
        ]);
        view()->share('platformCurrencyCode', $currencyCode);

        return $next($request);
    }
}
