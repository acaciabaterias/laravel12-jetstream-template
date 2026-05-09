<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\DrilldownAnalyticsComercial;
use App\Models\FaturaSaaS;
use App\Models\SnapshotAnalyticsComercial;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class CommercialAnalyticsDrilldownService
{
    public function rebuildForSnapshot(SnapshotAnalyticsComercial $snapshotAnalyticsComercial): void
    {
        $snapshotAnalyticsComercial->drilldowns()->delete();

        $limit = (int) config('platform_commercial_analytics.snapshot.drilldown_limit', 50);

        AssinaturaPlataforma::query()
            ->with('plano')
            ->whereIn('status', ['active', 'past_due', 'paused'])
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (AssinaturaPlataforma $assinaturaPlataforma) use ($snapshotAnalyticsComercial): void {
                DrilldownAnalyticsComercial::query()->create([
                    'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                    'source_type' => 'subscription',
                    'source_id' => $assinaturaPlataforma->id,
                    'dimension_type' => 'status',
                    'dimension_value' => $assinaturaPlataforma->status,
                    'metric_key' => 'mrr',
                    'metric_value' => (float) ($assinaturaPlataforma->plano?->preco_mensal ?? 0),
                    'metadata' => [
                        'cliente_id' => $assinaturaPlataforma->cliente_id,
                    ],
                ]);
            });

        FaturaSaaS::query()
            ->whereIn('status', ['pending', 'overdue'])
            ->latest('vencimento')
            ->limit($limit)
            ->get()
            ->each(function (FaturaSaaS $faturaSaaS) use ($snapshotAnalyticsComercial): void {
                DrilldownAnalyticsComercial::query()->create([
                    'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                    'source_type' => 'invoice',
                    'source_id' => $faturaSaaS->id,
                    'dimension_type' => 'billing_channel',
                    'dimension_value' => $faturaSaaS->billing_channel,
                    'metric_key' => 'delinquency',
                    'metric_value' => (float) $faturaSaaS->valor,
                    'metadata' => [
                        'cliente_id' => $faturaSaaS->cliente_id,
                        'status' => $faturaSaaS->status,
                    ],
                ]);
            });

        if (Schema::connection('central')->hasTable('casos_recuperacao_receita')) {
            CasoRecuperacaoReceita::query()
                ->with('fatura')
                ->where('status', RevenueRecoveryCaseStatus::Recovered->value)
                ->latest()
                ->limit($limit)
                ->get()
                ->each(function (CasoRecuperacaoReceita $casoRecuperacaoReceita) use ($snapshotAnalyticsComercial): void {
                    DrilldownAnalyticsComercial::query()->create([
                        'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                        'source_type' => 'recovery_case',
                        'source_id' => $casoRecuperacaoReceita->id,
                        'dimension_type' => 'stage',
                        'dimension_value' => $casoRecuperacaoReceita->current_stage,
                        'metric_key' => 'recovery',
                        'metric_value' => (float) ($casoRecuperacaoReceita->fatura?->valor ?? 0),
                        'metadata' => [
                            'cliente_id' => $casoRecuperacaoReceita->cliente_id,
                            'invoice_id' => $casoRecuperacaoReceita->fatura_saas_id,
                        ],
                    ]);
                });
        }
    }

    /**
     * @param  array{snapshot_id?:int|string|null,metric_key?:string|null,dimension_type?:string|null,source_type?:string|null,limit?:int|null}  $filters
     */
    public function inspect(array $filters = []): LengthAwarePaginator
    {
        $query = DrilldownAnalyticsComercial::query()
            ->with('snapshot')
            ->latest();

        if (filled($filters['snapshot_id'] ?? null)) {
            $query->where('snapshot_analytics_comercial_id', (int) $filters['snapshot_id']);
        }

        if (filled($filters['metric_key'] ?? null)) {
            $query->where('metric_key', (string) $filters['metric_key']);
        }

        if (filled($filters['dimension_type'] ?? null)) {
            $query->where('dimension_type', (string) $filters['dimension_type']);
        }

        if (filled($filters['source_type'] ?? null)) {
            $query->where('source_type', (string) $filters['source_type']);
        }

        return $query->paginate((int) ($filters['limit'] ?? 25));
    }
}
