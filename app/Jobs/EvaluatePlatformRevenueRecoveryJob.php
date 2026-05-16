<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\FaturaSaaS;
use App\Services\Billing\RevenueRecoveryCaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluatePlatformRevenueRecoveryJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $faturaSaaSId,
        public string $entryReason = 'invoice_overdue',
    ) {}

    public function handle(RevenueRecoveryCaseService $revenueRecoveryCaseService): void
    {
        $faturaSaaS = FaturaSaaS::query()->find($this->faturaSaaSId);

        if ($faturaSaaS === null) {
            return;
        }

        $revenueRecoveryCaseService->openForInvoice($faturaSaaS, $this->entryReason);
    }
}
