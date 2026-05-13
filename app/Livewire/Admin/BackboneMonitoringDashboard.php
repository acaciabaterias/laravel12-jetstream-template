<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\DashboardProvisioningRecord;
use App\Services\Operations\BackboneMonitoringInspectionService;
use App\Services\Operations\MonitoringProvisioningService;
use App\Services\Operations\MonitoringReadinessEvidenceService;
use App\Services\Operations\MonitoringReadinessService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class BackboneMonitoringDashboard extends Component
{
    #[Url(as: 'fluxo')]
    public string $flowNameFilter = '';

    #[Url(as: 'severidade')]
    public string $severityFilter = '';

    #[Url(as: 'alerta')]
    public string $alertStatusFilter = '';

    #[Url(as: 'ambiente')]
    public string $environmentFilter = '';

    public string $packageName = 'ops-overview';

    public string $packageVersion = '';

    public string $packageEnvironment = 'staging';

    public string $rollbackVersion = '';

    public ?string $operationMessage = null;

    public function rules(): array
    {
        return [
            'packageName' => ['required', 'string', 'max:120'],
            'packageVersion' => ['required', 'string', 'max:40'],
            'packageEnvironment' => ['required', 'string', 'max:40'],
            'rollbackVersion' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function rebuild(MonitoringReadinessService $monitoringReadinessService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-backbone-monitoring');

        $count = $monitoringReadinessService->refreshAll()->count();
        $this->operationMessage = sprintf('%d targets de monitoramento reavaliados com sucesso.', $count);
    }

    public function evaluateAlerts(BackboneMonitoringInspectionService $backboneMonitoringInspectionService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-backbone-monitoring');

        $count = count($backboneMonitoringInspectionService->evaluateRules([
            'flow_name' => $this->flowNameFilter,
            'severity' => $this->severityFilter,
            'alert_status' => $this->alertStatusFilter,
            'publish_events' => true,
        ]));

        $this->operationMessage = sprintf('%d regras de alerta avaliadas com sucesso.', $count);
    }

    public function registerPackage(MonitoringProvisioningService $monitoringProvisioningService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-backbone-monitoring');

        $validated = $this->validate($this->rules());
        $record = $monitoringProvisioningService->register([
            'package_name' => $validated['packageName'],
            'version' => $validated['packageVersion'],
            'environment' => $validated['packageEnvironment'],
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Pacote %s %s registrado para %s.', $record->package_name, $record->version, $record->environment);
    }

    public function applyPackage(int $recordId, MonitoringProvisioningService $monitoringProvisioningService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-backbone-monitoring');

        $record = DashboardProvisioningRecord::query()->findOrFail($recordId);
        $monitoringProvisioningService->markProvisioned($record, [
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Pacote %s aplicado com sucesso.', $record->package_name);
    }

    public function validatePackage(
        int $recordId,
        MonitoringProvisioningService $monitoringProvisioningService,
        MonitoringReadinessEvidenceService $monitoringReadinessEvidenceService,
    ): void {
        Gate::forUser(auth('platform')->user())->authorize('manage-backbone-monitoring');

        $record = DashboardProvisioningRecord::query()->findOrFail($recordId);
        $validatedRecord = $monitoringProvisioningService->markValidated($record, [
            'metadata' => ['source' => 'dashboard'],
        ]);
        $monitoringReadinessEvidenceService->recordForProvisioning($validatedRecord, [
            'evidence_type' => 'validation',
            'operator_user_id' => auth('platform')->id(),
            'result_status' => 'success',
            'notes' => 'Validacao operacional registrada pelo dashboard.',
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Pacote %s validado com evidencia registrada.', $record->package_name);
    }

    public function rollbackPackage(
        int $recordId,
        MonitoringProvisioningService $monitoringProvisioningService,
        MonitoringReadinessEvidenceService $monitoringReadinessEvidenceService,
    ): void {
        Gate::forUser(auth('platform')->user())->authorize('manage-backbone-monitoring');

        $this->validate([
            'rollbackVersion' => ['required', 'string', 'max:40'],
        ]);

        $record = DashboardProvisioningRecord::query()->findOrFail($recordId);
        $rolledBackRecord = $monitoringProvisioningService->rollback($record, [
            'rollback_version' => $this->rollbackVersion,
            'metadata' => ['source' => 'dashboard'],
        ]);
        $monitoringReadinessEvidenceService->recordForProvisioning($rolledBackRecord, [
            'evidence_type' => 'rollback',
            'operator_user_id' => auth('platform')->id(),
            'result_status' => 'success',
            'notes' => 'Rollback operacional registrado pelo dashboard.',
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Rollback do pacote %s registrado para %s.', $record->package_name, $this->rollbackVersion);
        $this->rollbackVersion = '';
    }

    public function render(
        MonitoringReadinessService $monitoringReadinessService,
        BackboneMonitoringInspectionService $backboneMonitoringInspectionService,
    ): View {
        Gate::forUser(auth('platform')->user())->authorize('manage-backbone-monitoring');

        $inspection = $backboneMonitoringInspectionService->inspect([
            'flow_name' => $this->flowNameFilter,
            'severity' => $this->severityFilter,
            'alert_status' => $this->alertStatusFilter,
            'environment' => $this->environmentFilter,
        ]);

        return view('livewire.admin.backbone-monitoring-dashboard', [
            'summary' => $monitoringReadinessService->summarize(),
            'targets' => $monitoringReadinessService->latestTargets($this->flowNameFilter),
            'alertRules' => $inspection['alert_rules'],
            'provisioningRecords' => $inspection['provisioning_records'],
            'readinessEvidences' => $inspection['readiness_evidences'],
            'operationMessage' => $this->operationMessage,
        ]);
    }
}
