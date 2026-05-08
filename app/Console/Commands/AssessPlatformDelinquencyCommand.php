<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AssinaturaPlataforma;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use Illuminate\Console\Command;

class AssessPlatformDelinquencyCommand extends Command
{
    protected $signature = 'platform-billing:assess-delinquency {--subscription-id= : Reavalia apenas uma assinatura especifica}';

    protected $description = 'Aplica grace period, bloqueio e reativacao das assinaturas SaaS centrais';

    public function handle(DelinquencyPolicyEvaluator $delinquencyPolicyEvaluator): int
    {
        $query = AssinaturaPlataforma::query()->whereIn('status', [
            'active',
            'grace_period',
            'blocked',
        ]);

        $subscriptionId = $this->option('subscription-id');
        if ($subscriptionId !== null) {
            $query->whereKey((int) $subscriptionId);
        }

        $processed = 0;

        foreach ($query->get() as $assinaturaPlataforma) {
            $delinquencyPolicyEvaluator->assess($assinaturaPlataforma);
            $processed++;
        }

        $this->info(sprintf('%d assinatura(s) avaliadas.', $processed));

        return self::SUCCESS;
    }
}
