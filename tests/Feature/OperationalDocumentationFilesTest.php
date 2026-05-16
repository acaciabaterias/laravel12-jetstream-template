<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class OperationalDocumentationFilesTest extends TestCase
{
    public function test_requested_operational_files_exist(): void
    {
        $files = [
            'BACKUP_GUIDE.md',
            'MONITORING_SETUP.md',
            'POST_DEPLOY_CHECKLIST.md',
            'ALERT_RULES.md',
            'GO_LIVE_RUNBOOK.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_operational_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Guia de Backup Completo - ERP BateriaExpert', (string) file_get_contents(base_path('BACKUP_GUIDE.md')));
        $this->assertStringContainsString('# Configuracao de Monitoramento - UptimeRobot e Prometheus', (string) file_get_contents(base_path('MONITORING_SETUP.md')));
        $this->assertStringContainsString('# Checklist Pos-Deploy - ERP BateriaExpert', (string) file_get_contents(base_path('POST_DEPLOY_CHECKLIST.md')));
        $this->assertStringContainsString('# Regras de Alerta Operacional - ERP BateriaExpert', (string) file_get_contents(base_path('ALERT_RULES.md')));
        $this->assertStringContainsString('# Runbook de Go-Live e Rollback - ERP BateriaExpert', (string) file_get_contents(base_path('GO_LIVE_RUNBOOK.md')));
    }

    public function test_go_live_runbook_contains_deploy_validation_and_rollback_steps(): void
    {
        $runbook = file_get_contents(base_path('GO_LIVE_RUNBOOK.md'));

        $this->assertIsString($runbook);
        $this->assertStringContainsString('## 1. Pre-Flight', $runbook);
        $this->assertStringContainsString('./validate-env.sh', $runbook);
        $this->assertStringContainsString('./backup.sh', $runbook);
        $this->assertStringContainsString('php artisan migrate --database=central --path=database/migrations/central --force --no-interaction', $runbook);
        $this->assertStringContainsString('php artisan tenant:migrate-all --force', $runbook);
        $this->assertStringContainsString('kubectl kustomize infra/kubernetes/production', $runbook);
        $this->assertStringContainsString('## 5. Go/No-Go', $runbook);
        $this->assertStringContainsString('## 6. Rollback', $runbook);
        $this->assertStringContainsString('./restore.sh', $runbook);
    }
}
