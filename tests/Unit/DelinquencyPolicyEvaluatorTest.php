<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FaturaSaaS;
use App\Models\PoliticaInadimplencia;
use App\Services\Billing\DelinquencyPolicyEvaluator;
use App\Support\Billing\DelinquencyAction;
use App\Support\Billing\PlatformSubscriptionStatus;
use App\Support\Billing\SaasInvoiceStatus;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class DelinquencyPolicyEvaluatorTest extends TestCase
{
    private function makeInvoice(string $status, Carbon $dueDate): FaturaSaaS
    {
        $fatura = new FaturaSaaS;
        $fatura->setRawAttributes([
            'status' => $status,
            'vencimento' => $dueDate->toDateString(),
        ], true);

        return $fatura;
    }

    private function makePolicy(int $gracePeriodDays, int $blockAfterDays): PoliticaInadimplencia
    {
        $politica = new PoliticaInadimplencia;
        $politica->setRawAttributes([
            'grace_period_days' => $gracePeriodDays,
            'block_after_days' => $blockAfterDays,
            'reactivation_mode' => 'automatic',
            'notification_profile' => '[]',
        ], true);

        return $politica;
    }

    public function test_it_marks_a_pending_invoice_as_overdue_once_the_due_date_has_passed(): void
    {
        $fatura = $this->makeInvoice(SaasInvoiceStatus::Pending->value, Carbon::now()->subDay());

        $acao = (new DelinquencyPolicyEvaluator)->decideAction(
            faturaSaaS: $fatura,
            politicaInadimplencia: $this->makePolicy(3, 7),
            currentStatus: PlatformSubscriptionStatus::Active->value,
            referenceDate: Carbon::now(),
        );

        $this->assertSame(DelinquencyAction::MarkOverdue, $acao);
    }

    public function test_it_blocks_when_the_invoice_has_exceeded_the_block_threshold(): void
    {
        $fatura = $this->makeInvoice(SaasInvoiceStatus::Overdue->value, Carbon::now()->subDays(10));

        $acao = (new DelinquencyPolicyEvaluator)->decideAction(
            faturaSaaS: $fatura,
            politicaInadimplencia: $this->makePolicy(2, 5),
            currentStatus: PlatformSubscriptionStatus::GracePeriod->value,
            referenceDate: Carbon::now(),
        );

        $this->assertSame(DelinquencyAction::BlockSubscriber, $acao);
    }

    public function test_it_reactivates_when_the_open_invoice_is_missing_and_subscription_is_blocked(): void
    {
        $acao = (new DelinquencyPolicyEvaluator)->decideAction(
            faturaSaaS: null,
            politicaInadimplencia: $this->makePolicy(2, 5),
            currentStatus: PlatformSubscriptionStatus::Blocked->value,
            referenceDate: Carbon::now(),
        );

        $this->assertSame(DelinquencyAction::ReactivateSubscriber, $acao);
    }
}
