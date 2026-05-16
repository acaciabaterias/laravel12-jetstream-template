<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\ExecutiveReportingDashboard;
use App\Models\ExecutiveReportExport;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithExecutiveReportingFixtures;
use Tests\TestCase;

class ExecutiveReportingReexecutionTest extends TestCase
{
    use InteractsWithExecutiveReportingFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->runExecutiveReportingMigrations();
        $this->seedExecutiveReportingScenario();
    }

    public function test_it_reexecutes_a_previous_export_with_auditable_history(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($operator, 'platform');

        Livewire::test(ExecutiveReportingDashboard::class)
            ->call('exportExcel');

        $firstExport = ExecutiveReportExport::query()->latest('id')->firstOrFail();

        Livewire::test(ExecutiveReportingDashboard::class)
            ->call('reexecuteExport', $firstExport->id);

        $reexecuted = ExecutiveReportExport::query()->latest('id')->firstOrFail();

        $this->assertNotSame($firstExport->id, $reexecuted->id);
        $this->assertSame($firstExport->id, $reexecuted->reexecuted_from_export_id);
        $this->assertSame('reexecuted', $reexecuted->export_status->value);
        $this->assertDatabaseHas('executive_report_execution_logs', [
            'executive_report_export_id' => $reexecuted->id,
            'event_type' => 'reexecuted',
        ], 'central');
    }
}
