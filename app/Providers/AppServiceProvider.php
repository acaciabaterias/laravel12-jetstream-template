<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        seo()
            ->site('Promovaweb')
            ->title(
                default: 'Laravel 12 Jetstream Livewire Starter Kit',
                modify: fn (string $title) => $title.' | Promovaweb'
            )
            ->description(default: 'We are a development agency ...')
            ->twitterSite('@promovaweb');
    }
}
