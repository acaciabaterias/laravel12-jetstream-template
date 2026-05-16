<?php

namespace App\Jobs;

use App\Services\DeliverySyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDeliveryEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload) {}

    public function handle(DeliverySyncService $deliverySyncService): void
    {
        $deliverySyncService->sync($this->payload);
    }
}
