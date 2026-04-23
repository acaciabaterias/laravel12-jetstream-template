<?php

namespace App\Jobs;

use App\Models\ContaBancaria;
use App\Services\BankApiClient;
use App\Services\FinanceMatcherProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBankTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $contaBancariaId) {}

    public function handle(BankApiClient $bankApiClient, FinanceMatcherProcessor $financeMatcherProcessor): void
    {
        $conta = ContaBancaria::query()->findOrFail($this->contaBancariaId);
        $financeMatcherProcessor->importAndMatch($conta, $bankApiClient->fetchTransactions());
    }
}
