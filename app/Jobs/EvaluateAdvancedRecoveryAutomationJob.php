<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CasoRecuperacaoReceita;
use App\Services\Billing\AdvancedRecoveryAutomationDispatchScheduler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluateAdvancedRecoveryAutomationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $casoRecuperacaoReceitaId,
    ) {}

    public function handle(AdvancedRecoveryAutomationDispatchScheduler $dispatchScheduler): void
    {
        $recoveryCase = CasoRecuperacaoReceita::query()->find($this->casoRecuperacaoReceitaId);

        if ($recoveryCase === null) {
            return;
        }

        $dispatchScheduler->schedule($recoveryCase);
    }
}
