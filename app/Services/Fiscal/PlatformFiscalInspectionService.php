<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRulePublicationRecord;

class PlatformFiscalInspectionService
{
    public function __construct(
        private readonly PlatformFiscalScenarioLookupService $platformFiscalScenarioLookupService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function inspect(array $filters = []): array
    {
        $lookupInspection = $this->platformFiscalScenarioLookupService->inspect($filters);

        $issues = FiscalRuleIssueReport::query()
            ->when(filled($filters['scenario'] ?? null), fn ($query) => $query->where('scenario_key', $filters['scenario']))
            ->when(filled($filters['severity'] ?? null), fn ($query) => $query->where('severity', $filters['severity']))
            ->when(filled($filters['issue_type'] ?? null), fn ($query) => $query->where('issue_type', $filters['issue_type']))
            ->latest('detected_at')
            ->limit((int) ($filters['limit'] ?? 10))
            ->get();

        $publications = FiscalRulePublicationRecord::query()
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->latest('published_at')
            ->latest('id')
            ->limit((int) ($filters['publication_limit'] ?? 10))
            ->get();

        return [
            'summary' => [
                'release_key' => $lookupInspection['summary']['release_key'] ?? null,
                'required_scenarios' => $lookupInspection['summary']['required_scenarios'] ?? 0,
                'covered_scenarios' => $lookupInspection['summary']['covered_scenarios'] ?? 0,
                'active_publications' => FiscalRulePublicationRecord::query()->where('status', 'active')->count(),
                'draft_publications' => FiscalRulePublicationRecord::query()->where('status', 'draft')->count(),
                'rolled_back_publications' => FiscalRulePublicationRecord::query()->where('status', 'rolled_back')->count(),
                'open_issues' => FiscalRuleIssueReport::query()->where('resolution_status', 'open')->count(),
                'critical_issues' => FiscalRuleIssueReport::query()
                    ->where('resolution_status', 'open')
                    ->where('severity', 'critical')
                    ->count(),
                'material_tax_issues' => FiscalRuleIssueReport::query()
                    ->where('resolution_status', 'open')
                    ->whereIn('issue_type', ['material_tax_profile_missing', 'tax_profile_gap', 'missing_interstate_rate', 'missing_cst', 'missing_csosn'])
                    ->count(),
                'interstate_profiles' => $lookupInspection['active_publication']?->taxProfiles()
                    ->whereNotNull('origin_state')
                    ->whereNotNull('destination_state')
                    ->whereColumn('origin_state', '!=', 'destination_state')
                    ->count() ?? 0,
                'lookup_issue_code' => $lookupInspection['lookup']['issue']['code'] ?? null,
            ],
            'lookup' => $lookupInspection['lookup'],
            'consumer_contract' => $lookupInspection['consumer_contract'],
            'scenarios' => $lookupInspection['scenarios'],
            'issues' => $issues,
            'publications' => $publications,
            'active_publication' => $lookupInspection['active_publication'],
        ];
    }
}
