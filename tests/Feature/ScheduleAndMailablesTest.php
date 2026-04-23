<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\AtualizarIndiceRetornoJob;
use App\Jobs\SincronizarEstoqueComSucataJob;
use App\Mail\BillingOverdueMail;
use App\Mail\GuaranteeStatusUpdatedMail;
use App\Mail\LowStockAlertMail;
use App\Mail\WelcomeTenantMail;
use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\OrdemServicoGarantia;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ScheduleAndMailablesTest extends TestCase
{
    public function test_schedule_registers_tenant_commands_and_operational_jobs(): void
    {
        $events = collect(app(Schedule::class)->events());

        $commandSummaries = $events
            ->map(fn ($event): string => trim((string) ($event->command ?? $event->description ?? '')))
            ->filter()
            ->values();

        $jobSummaries = $events
            ->map(fn ($event): string => $event->description ?? '')
            ->filter()
            ->values();

        $this->assertTrue($commandSummaries->contains(fn (string $value): bool => str_contains($value, 'tenant:health --json')));
        $this->assertTrue($commandSummaries->contains(fn (string $value): bool => str_contains($value, 'tenant:list --status=active --json')));
        $this->assertTrue($commandSummaries->contains(fn (string $value): bool => str_contains($value, 'tenant:migrate-all --force')));
        $this->assertTrue($commandSummaries->contains(fn (string $value): bool => str_contains($value, 'tenant:backup --all')));
        $this->assertTrue($jobSummaries->contains(AtualizarIndiceRetornoJob::class));
        $this->assertTrue($jobSummaries->contains(SincronizarEstoqueComSucataJob::class));
    }

    public function test_welcome_tenant_mail_renders_expected_context(): void
    {
        $tenant = Cliente::factory()->make([
            'razao_social' => 'BateriaExpert Sul',
            'subdominio' => 'sul',
            'plano' => 'pro',
        ]);

        $mail = new WelcomeTenantMail($tenant, 'https://admin.example.com/painel');

        $this->assertSame('Bem-vindo ao BateriaExpert', $mail->envelope()->subject);
        $this->assertStringContainsString('BateriaExpert Sul', $mail->render());
        $this->assertStringContainsString('https://admin.example.com/painel', $mail->render());
    }

    public function test_low_stock_alert_mail_renders_battery_information(): void
    {
        $bateria = new Bateria([
            'sku' => 'BAT-100',
            'marca' => 'Moura',
        ]);

        $mail = new LowStockAlertMail($bateria, 3, 'https://erp.example.com/dashboard');

        $this->assertSame('Alerta de estoque baixo', $mail->envelope()->subject);
        $this->assertStringContainsString('BAT-100', $mail->render());
        $this->assertStringContainsString('3 unidades', $mail->render());
    }

    public function test_billing_and_guarantee_mailables_render_expected_content(): void
    {
        $tenant = Cliente::factory()->make([
            'razao_social' => 'BateriaExpert Centro',
            'subdominio' => 'centro',
        ]);

        $ordemServico = new OrdemServicoGarantia([
            'status' => 'concluida',
            'resultado' => 'procedente',
        ]);
        $ordemServico->forceFill(['id' => 42]);

        $billingMail = new BillingOverdueMail($tenant, 'https://admin.example.com/financeiro');
        $guaranteeMail = new GuaranteeStatusUpdatedMail($ordemServico, 'https://erp.example.com/garantias');

        $this->assertSame('Atenção: assinatura em atraso', $billingMail->envelope()->subject);
        $this->assertStringContainsString('BateriaExpert Centro', $billingMail->render());
        $this->assertStringContainsString('https://admin.example.com/financeiro', $billingMail->render());

        $this->assertSame('Atualização da sua garantia', $guaranteeMail->envelope()->subject);
        $this->assertStringContainsString('#42', $guaranteeMail->render());
        $this->assertStringContainsString('procedente', $guaranteeMail->render());
    }
}
