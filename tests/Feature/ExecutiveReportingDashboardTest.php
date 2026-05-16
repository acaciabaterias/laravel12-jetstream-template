<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\ExecutiveReportingDashboard;
use App\Models\UsuarioPlataforma;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithExecutiveReportingFixtures;
use Tests\TestCase;

class ExecutiveReportingDashboardTest extends TestCase
{
    use InteractsWithExecutiveReportingFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runExecutiveReportingMigrations();
        $this->seedExecutiveReportingScenario();
    }

    public function test_billing_operator_can_view_the_executive_reporting_dashboard(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();

        $response = $this
            ->actingAs($operator, 'platform')
            ->get(route('admin.reports.index'));

        $response
            ->assertOk()
            ->assertSee('Executive reporting hub')
            ->assertSeeLivewire(ExecutiveReportingDashboard::class);
    }

    public function test_support_user_cannot_render_the_executive_reporting_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $this->actingAs($support, 'platform');

        Livewire::test(ExecutiveReportingDashboard::class)
            ->assertForbidden();
    }
}
