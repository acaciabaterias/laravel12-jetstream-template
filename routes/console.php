<?php

use App\Jobs\AtualizarIndiceRetornoJob;
use App\Jobs\SincronizarEstoqueComSucataJob;
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

Schedule::command('integration:dispatch-outbox --limit=200')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('platform-billing:assess-delinquency')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::job(new AtualizarIndiceRetornoJob)
    ->dailyAt('01:30')
    ->withoutOverlapping();

Schedule::job(new SincronizarEstoqueComSucataJob)
    ->everyThirtyMinutes()
    ->withoutOverlapping();

Schedule::command('audit:cleanup --days=90')
    ->weeklyOn(0, '04:00') // Domingos às 04:00
    ->withoutOverlapping()
    ->onOneServer();
