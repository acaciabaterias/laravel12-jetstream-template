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
    }
}
