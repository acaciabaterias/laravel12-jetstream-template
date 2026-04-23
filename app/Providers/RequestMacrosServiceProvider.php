<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class RequestMacrosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Request::macro('isMobile', function (): bool {
            /** @var Request $this */
            $userAgent = strtolower((string) $this->userAgent());

            return str_contains($userAgent, 'mobile')
                || str_contains($userAgent, 'android')
                || str_contains($userAgent, 'iphone')
                || str_contains($userAgent, 'ipad');
        });

        Request::macro('isAjax', function (): bool {
            /** @var Request $this */
            return $this->ajax()
                || $this->expectsJson()
                || $this->isJson()
                || str_starts_with($this->path(), 'api/');
        });
    }
}
