<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\EstoqueBaixoEvent;
use App\Events\OSConcluidaEvent;
use App\Events\ValeCancelado;
use App\Events\ValeCriado;
use App\Listeners\AtualizarIndiceRetornoListener;
use App\Listeners\EstornarEstoqueListener;
use App\Listeners\NotificarComprasListener;
use App\Listeners\ReservarEstoqueListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Registra os eventos de dominio do ERP.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ValeCriado::class => [
            ReservarEstoqueListener::class,
        ],
        ValeCancelado::class => [
            EstornarEstoqueListener::class,
        ],
        EstoqueBaixoEvent::class => [
            NotificarComprasListener::class,
        ],
        OSConcluidaEvent::class => [
            AtualizarIndiceRetornoListener::class,
        ],
    ];
}
