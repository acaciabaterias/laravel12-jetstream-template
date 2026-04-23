<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TenantCommandsTest extends TestCase
{
    public function test_tenant_list_displays_registered_tenants(): void
    {
        $cliente = Cliente::factory()->create([
            'razao_social' => 'Bateria Expert Norte',
            'subdominio' => 'norte',
            'status' => 'active',
        ]);

        $this->artisan('tenant:list')
            ->expectsTable(
                ['ID', 'Razão Social', 'Subdomínio', 'Status', 'Plano', 'Expira em'],
                [[
                    $cliente->id,
                    'Bateria Expert Norte',
                    'norte',
                    'active',
                    'essential',
                    '-',
                ]],
            )
            ->assertSuccessful();
    }

    public function test_tenant_health_reports_healthy_tenant(): void
    {
        Cliente::factory()->create([
            'subdominio' => 'saudavel',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $this->artisan('tenant:health saudavel')
            ->expectsTable(
                ['Tenant', 'Status', 'Subscription', 'Database'],
                [['saudavel', 'healthy', 'ok', 'configured']],
            )
            ->assertSuccessful();
    }

    public function test_tenant_backup_supports_pretend_mode(): void
    {
        $cliente = Cliente::factory()->create([
            'subdominio' => 'backup-demo',
            'supabase_db_host' => 'db.backup-demo.supabase.co',
            'supabase_db_password' => 'secret-pass',
        ]);

        $backupPath = storage_path('app/testing-backups');
        File::deleteDirectory($backupPath);

        $this->artisan('tenant:backup', [
            'tenant' => (string) $cliente->id,
            '--path' => $backupPath,
            '--pretend' => true,
        ])->assertSuccessful();

        $this->assertDirectoryExists($backupPath);
    }
}
