<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tenant:health --json')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('tenant:list --status=active --json')
    ->dailyAt('06:00')
    ->withoutOverlapping();

Schedule::command('tenant:migrate-all --force')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('tenant:backup --all')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::job(new \App\Jobs\AtualizarIndiceRetornoJob)
    ->dailyAt('01:30')
    ->withoutOverlapping();

Schedule::job(new \App\Jobs\SincronizarEstoqueComSucataJob)
    ->everyThirtyMinutes()
    ->withoutOverlapping();
