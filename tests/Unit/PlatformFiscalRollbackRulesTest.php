<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FiscalRulePublicationRecord;
use App\Services\Fiscal\PlatformFiscalRollbackRules;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PlatformFiscalRollbackRulesTest extends TestCase
{
    public function test_it_finds_the_previous_restorable_publication(): void
    {
        $rules = app(PlatformFiscalRollbackRules::class);
        $current = (new FiscalRulePublicationRecord)->forceFill([
            'id' => 20,
            'status' => FiscalRulePublicationStatus::Active->value,
        ]);
        $baseline = (new FiscalRulePublicationRecord)->forceFill([
            'id' => 10,
            'status' => FiscalRulePublicationStatus::Superseded->value,
        ]);

        $restorablePublication = $rules->findRestorablePublication(new Collection([$current, $baseline]), 20);

        $this->assertSame(10, $restorablePublication?->id);
    }

    public function test_it_only_allows_rollback_when_the_active_bundle_has_open_critical_issues_and_a_baseline(): void
    {
        $rules = app(PlatformFiscalRollbackRules::class);
        $current = (new FiscalRulePublicationRecord)->forceFill([
            'status' => FiscalRulePublicationStatus::Active->value,
        ]);
        $baseline = (new FiscalRulePublicationRecord)->forceFill([
            'status' => FiscalRulePublicationStatus::Superseded->value,
        ]);

        $this->assertTrue($rules->canRollback($current, $baseline, 1));
        $this->assertFalse($rules->canRollback($current, $baseline, 0));
        $this->assertFalse($rules->canRollback($current, null, 1));
    }
}
