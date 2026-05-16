<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\EvaluatePlatformRevenueRecoveryJob;
use App\Models\FaturaSaaS;
use Illuminate\Console\Command;

class EvaluatePlatformRevenueRecoveryCommand extends Command
{
    protected $signature = 'platform-revenue-recovery:evaluate {invoice_id? : ID da fatura SaaS} {--reason=invoice_overdue : Motivo de entrada na regua}';

    protected $description = 'Avalia faturas SaaS elegiveis e abre casos de recuperacao de receita';

    public function handle(): int
    {
        $reason = (string) $this->option('reason');
        $invoiceId = $this->argument('invoice_id');

        if ($invoiceId !== null) {
            $invoice = FaturaSaaS::query()->find($invoiceId);

            if ($invoice === null) {
                $this->error('Fatura SaaS nao encontrada.');

                return self::FAILURE;
            }

            EvaluatePlatformRevenueRecoveryJob::dispatchSync($invoice->id, $reason);

            $this->info(sprintf('Recuperacao avaliada para a fatura %d.', $invoice->id));

            return self::SUCCESS;
        }

        $processed = 0;

        FaturaSaaS::query()
            ->where('status', 'pending')
            ->whereDate('vencimento', '<', now()->toDateString())
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->each(function (FaturaSaaS $faturaSaaS) use (&$processed, $reason): void {
                EvaluatePlatformRevenueRecoveryJob::dispatchSync($faturaSaaS->id, $reason);
                $processed++;
            });

        $this->info(sprintf('%d fatura(s) avaliadas para recuperacao.', $processed));

        return self::SUCCESS;
    }
}
