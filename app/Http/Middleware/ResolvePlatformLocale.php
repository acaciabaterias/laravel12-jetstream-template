<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UsuarioPlataforma;
use App\Services\Platform\PlatformLocaleResolutionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ResolvePlatformLocale
{
    public function __construct(
        private readonly PlatformLocaleResolutionService $platformLocaleResolutionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var UsuarioPlataforma|null $user */
        $user = auth('platform')->user();
        $locale = $this->platformLocaleResolutionService->resolve($user, $request->session());

        App::setLocale($locale);
        $request->session()->put('platform_locale', $locale);

        return $next($request);
    }
}
