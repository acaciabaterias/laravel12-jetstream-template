<?php

namespace App\Console\Commands;

use App\Jobs\DispatchOutboxEventJob;
use App\Models\EventoOutbox;
use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Console\Command;

class DispatchPendingOutboxEventsCommand extends Command
{
    protected $signature = 'integration:dispatch-outbox {--limit=100 : Quantidade maxima de eventos por varredura}';

    protected $description = 'Despacha eventos pendentes da outbox de integracao';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $events = EventoOutbox::query()
            ->where('status', IntegrationFlowStatus::Pending)
            ->where(function ($query): void {
                $query->whereNull('available_at')
                    ->orWhere('available_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($events as $event) {
            DispatchOutboxEventJob::dispatch($event->id)
                ->onQueue(config('services.integration_backbone.broker.outbox_queue'));
        }

        $this->info(sprintf('%d evento(s) enviados para dispatch.', $events->count()));

        return self::SUCCESS;
    }
}
