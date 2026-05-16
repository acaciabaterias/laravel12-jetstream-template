<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\ExecutiveAnalyticsSnapshot;
use App\Models\ExecutiveReportDefinition;
use App\Models\ExecutiveReportExport;
use App\Support\Billing\ExecutiveReportFormat;
use App\Support\Billing\ExecutiveReportPdfBuilder;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ExecutiveReportArtifactService
{
    public function __construct(
        private readonly ExecutiveReportPdfBuilder $executiveReportPdfBuilder,
    ) {}

    public function generate(
        ExecutiveReportExport $executiveReportExport,
        ExecutiveAnalyticsSnapshot $executiveAnalyticsSnapshot,
        ExecutiveReportDefinition $executiveReportDefinition,
    ): string {
        $disk = config('executive_reporting.storage_disk', 'local');
        $directory = trim((string) config('executive_reporting.storage_directory', 'executive-reports'), '/');
        Storage::disk($disk)->makeDirectory($directory);

        $relativePath = sprintf(
            '%s/%s-%d.%s',
            $directory,
            $this->buildArtifactBasename($executiveReportDefinition->slug, $executiveReportExport->id),
            $executiveReportExport->id,
            $executiveReportExport->format->extension()
        );
        $absolutePath = Storage::disk($disk)->path($relativePath);

        if ($executiveReportExport->format === ExecutiveReportFormat::Excel) {
            $this->writeExcel($absolutePath, $this->rowsForSnapshot($executiveAnalyticsSnapshot, $executiveReportDefinition));
        } else {
            Storage::disk($disk)->put($relativePath, $this->executiveReportPdfBuilder->build(
                $this->linesForSnapshot($executiveAnalyticsSnapshot, $executiveReportDefinition)
            ));
        }

        return $relativePath;
    }

    public function buildArtifactBasename(string $slug, int $exportId): string
    {
        return sprintf('%s-report-%d', $slug, $exportId);
    }

    /**
     * @return array<int, array<int, null|bool|float|int|string>>
     */
    public function rowsForSnapshot(
        ExecutiveAnalyticsSnapshot $executiveAnalyticsSnapshot,
        ExecutiveReportDefinition $executiveReportDefinition,
    ): array {
        $summary = $executiveAnalyticsSnapshot->kpi_payload ?? [];
        $drilldowns = $executiveAnalyticsSnapshot->drilldown_payload ?? [];

        $rows = [
            [$executiveReportDefinition->name, ''],
            ['Periodo', sprintf('%s a %s', $executiveAnalyticsSnapshot->period_start?->toDateString(), $executiveAnalyticsSnapshot->period_end?->toDateString())],
            ['MRR', (string) ($summary['mrr'] ?? 0)],
            ['Assinaturas ativas', (string) ($summary['active_subscriptions'] ?? 0)],
            ['Assinaturas bloqueadas', (string) ($summary['blocked_subscriptions'] ?? 0)],
            ['Exposicao vencida', (string) ($summary['overdue_exposure'] ?? 0)],
            ['Valor recuperado', (string) ($summary['recovered_amount'] ?? 0)],
            ['Falhas de pagamento', (string) ($summary['payment_failures'] ?? 0)],
            ['Casos abertos de recovery', (string) ($summary['open_recovery_cases'] ?? 0)],
            ['Contas em risco', (string) ($summary['at_risk_accounts'] ?? 0)],
            ['Secao', 'Planos'],
        ];

        foreach (($drilldowns['plans'] ?? []) as $plan) {
            $rows[] = [
                (string) ($plan['label'] ?? $plan['key'] ?? 'plano'),
                sprintf('%s assinaturas | MRR %s', $plan['subscriptions'] ?? 0, $plan['mrr'] ?? 0),
            ];
        }

        $rows[] = ['Secao', 'Canais'];
        foreach (($drilldowns['channels'] ?? []) as $channel) {
            $rows[] = [
                (string) ($channel['label'] ?? $channel['key'] ?? 'canal'),
                sprintf('%s cobrancas | Valor %s', $channel['invoices'] ?? 0, $channel['amount'] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public function linesForSnapshot(
        ExecutiveAnalyticsSnapshot $executiveAnalyticsSnapshot,
        ExecutiveReportDefinition $executiveReportDefinition,
    ): array {
        return collect($this->rowsForSnapshot($executiveAnalyticsSnapshot, $executiveReportDefinition))
            ->map(fn (array $row): string => trim(implode(': ', array_filter($row, fn ($value): bool => $value !== ''))))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<int, null|bool|float|int|string>>  $rows
     */
    private function writeExcel(string $absolutePath, array $rows): void
    {
        $writer = new Writer;
        $writer->openToFile($absolutePath);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
    }
}
