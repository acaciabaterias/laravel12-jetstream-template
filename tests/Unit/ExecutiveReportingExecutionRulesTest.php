<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Billing\ExecutiveReportExecutionClassifier;
use Tests\NonDatabaseTestCase;

class ExecutiveReportingExecutionRulesTest extends NonDatabaseTestCase
{
    public function test_it_classifies_completed_and_reexecuted_exports(): void
    {
        $classifier = app(ExecutiveReportExecutionClassifier::class);

        $this->assertSame('completed', $classifier->statusFor(false)->value);
        $this->assertSame('reexecuted', $classifier->statusFor(true)->value);
        $this->assertSame('completed', $classifier->completionEventFor(false));
        $this->assertSame('reexecuted', $classifier->completionEventFor(true));
    }
}
