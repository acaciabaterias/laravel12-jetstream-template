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

class ExecutiveReportingExcelExportTest extends TestCase
{
    use InteractsWithExecutiveReportingFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->runExecutiveReportingMigrations();
        $this->seedExecutiveReportingScenario();
    }

    public function test_it_generates_an_excel_export_from_the_current_executive_recorte(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($operator, 'platform');

        Livewire::test(ExecutiveReportingDashboard::class)
            ->call('exportExcel');

        $export = ExecutiveReportExport::query()->latest('id')->firstOrFail();

        $this->assertSame('excel', $export->format->value);
        $this->assertSame('completed', $export->export_status->value);
        $this->assertNotNull($export->file_reference);
        Storage::disk('local')->assertExists($export->file_reference);
    }
}
