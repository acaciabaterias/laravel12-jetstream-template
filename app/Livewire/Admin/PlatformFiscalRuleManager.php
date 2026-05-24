<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Http\Requests\Admin\StorePlatformFiscalPublicationRequest;
use App\Models\FiscalRulePublicationRecord;
use App\Services\Fiscal\PlatformFiscalInspectionService;
use App\Services\Fiscal\PlatformFiscalPublicationService;
use App\Services\Fiscal\PlatformFiscalRollbackService;
use App\Services\Fiscal\PlatformFiscalScenarioLookupService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class PlatformFiscalRuleManager extends Component
{
    #[Url(as: 'scenario')]
    public string $scenarioFilter = 'direct_export';

    #[Url(as: 'severity')]
    public string $severityFilter = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $catalogEntries = [
        [
            'cfop_code' => '7101',
            'description' => 'Direct export of own production',
            'operation_direction' => 'export',
        ],
        [
            'cfop_code' => '7501',
            'description' => 'Indirect export remittance',
            'operation_direction' => 'export',
        ],
        [
            'cfop_code' => '3101',
            'description' => 'Import for resale',
            'operation_direction' => 'import',
        ],
        [
            'cfop_code' => '3551',
            'description' => 'Import for industrialization',
            'operation_direction' => 'import',
        ],
    ];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $scenarioMappings = [
        [
            'scenario_key' => 'direct_export',
            'cfop_code' => '7101',
            'classification_code' => '85072010',
            'operation_direction' => 'export',
            'validation_flags' => ['requires_ncm', 'requires_foreign_partner'],
            'tax_profile' => [
                'ncm_code' => '85072010',
                'tax_regime' => 'regular',
                'cst_code' => '041',
                'csosn_code' => null,
                'partner_type' => 'customer',
                'operation_purpose' => 'direct_export',
                'origin_state' => null,
                'destination_state' => null,
                'interstate_tax_rate' => null,
                'tax_payload' => ['ipi_rate' => 0, 'pis_rate' => 0, 'cofins_rate' => 0],
            ],
        ],
        [
            'scenario_key' => 'indirect_export',
            'cfop_code' => '7501',
            'classification_code' => '85072010',
            'operation_direction' => 'export',
            'validation_flags' => ['requires_export_commitment'],
            'tax_profile' => [
                'ncm_code' => '85072010',
                'tax_regime' => 'regular',
                'cst_code' => '041',
                'csosn_code' => null,
                'partner_type' => 'trading_company',
                'operation_purpose' => 'indirect_export',
                'origin_state' => null,
                'destination_state' => null,
                'interstate_tax_rate' => null,
                'tax_payload' => ['ipi_rate' => 0, 'pis_rate' => 0, 'cofins_rate' => 0],
            ],
        ],
        [
            'scenario_key' => 'resale_import',
            'cfop_code' => '3101',
            'classification_code' => '85072010',
            'operation_direction' => 'import',
            'validation_flags' => ['requires_customs_record'],
            'tax_profile' => [
                'ncm_code' => '85072010',
                'tax_regime' => 'regular',
                'cst_code' => '040',
                'csosn_code' => null,
                'partner_type' => 'supplier',
                'operation_purpose' => 'resale',
                'origin_state' => null,
                'destination_state' => null,
                'interstate_tax_rate' => null,
                'tax_payload' => ['ii_rate' => 14, 'ipi_rate' => 0, 'pis_rate' => 0, 'cofins_rate' => 0],
            ],
        ],
        [
            'scenario_key' => 'industrial_import',
            'cfop_code' => '3551',
            'classification_code' => '85072010',
            'operation_direction' => 'import',
            'validation_flags' => ['requires_ncm'],
            'tax_profile' => [
                'ncm_code' => '85072010',
                'tax_regime' => 'regular',
                'cst_code' => '000',
                'csosn_code' => null,
                'partner_type' => 'supplier',
                'operation_purpose' => 'industrialization',
                'origin_state' => null,
                'destination_state' => null,
                'interstate_tax_rate' => null,
                'tax_payload' => ['ii_rate' => 14, 'ipi_rate' => 5, 'pis_rate' => 1.65, 'cofins_rate' => 7.6],
            ],
        ],
    ];

    public ?string $operationMessage = null;

    public string $rollbackReason = '';

    public function publishRules(PlatformFiscalPublicationService $platformFiscalPublicationService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-fiscal-rules');

        $request = new StorePlatformFiscalPublicationRequest;
        $validated = $this->validate($request->rules(), $request->messages());
        $normalizedMappings = collect($validated['scenarioMappings'])
            ->map(function (array $scenarioMapping): array {
                $validationFlags = $scenarioMapping['validation_flags'] ?? [];

                if (! is_array($validationFlags)) {
                    $validationFlags = array_filter(array_map('trim', explode(',', (string) $validationFlags)));
                }

                return [
                    'scenario_key' => $scenarioMapping['scenario_key'],
                    'cfop_code' => $scenarioMapping['cfop_code'],
                    'classification_code' => $scenarioMapping['classification_code'] ?? null,
                    'operation_direction' => $scenarioMapping['operation_direction'],
                    'validation_flags' => array_values($validationFlags),
                    'tax_profile' => $scenarioMapping['tax_profile'] ?? [],
                ];
            })
            ->values()
            ->all();

        $publication = $platformFiscalPublicationService->publish(
            $validated['catalogEntries'],
            $normalizedMappings,
            auth('platform')->id(),
        );

        $this->operationMessage = $publication->status->value === 'active'
            ? 'Publish fiscal bundle'
            : 'Register degraded fiscal bundle';
    }

    public function rollbackPublication(int $publicationId, PlatformFiscalRollbackService $platformFiscalRollbackService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('rollback-platform-fiscal-rules');

        $validated = $this->validate([
            'rollbackReason' => ['required', 'string', 'max:255'],
        ]);

        $publication = FiscalRulePublicationRecord::query()->findOrFail($publicationId);

        $platformFiscalRollbackService->rollback(
            $publication,
            $validated['rollbackReason'],
            auth('platform')->id(),
        );

        $this->operationMessage = 'Rollback fiscal publication';
    }

    public function render(
        PlatformFiscalScenarioLookupService $platformFiscalScenarioLookupService,
        PlatformFiscalInspectionService $platformFiscalInspectionService,
    ): View {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-fiscal-rules');

        $lookupInspection = $platformFiscalScenarioLookupService->inspect([
            'scenario' => $this->scenarioFilter,
            'severity' => $this->severityFilter,
            'limit' => 10,
        ]);
        $inspection = $platformFiscalInspectionService->inspect([
            'scenario' => $this->scenarioFilter,
            'severity' => $this->severityFilter,
            'status' => $this->statusFilter,
            'limit' => 10,
            'publication_limit' => 10,
        ]);

        return view('livewire.admin.platform-fiscal-rule-manager', [
            'summary' => $inspection['summary'],
            'lookup' => $lookupInspection['lookup'],
            'scenarios' => $lookupInspection['scenarios'],
            'issueReports' => $inspection['issues'],
            'activePublication' => $inspection['active_publication'],
            'publications' => $inspection['publications'],
            'supportedDirections' => (array) config('platform_fiscal_rules.supported_directions', []),
        ]);
    }
}
