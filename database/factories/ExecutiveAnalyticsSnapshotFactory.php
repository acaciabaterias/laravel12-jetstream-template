<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExecutiveAnalyticsSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExecutiveAnalyticsSnapshot>
 */
class ExecutiveAnalyticsSnapshotFactory extends Factory
{
    protected $model = ExecutiveAnalyticsSnapshot::class;

    public function definition(): array
    {
        return [
            'snapshot_key' => 'executive-overview',
            'reference_date' => now()->toDateString(),
            'period_start' => now()->subDays(29)->toDateString(),
            'period_end' => now()->toDateString(),
            'filter_hash' => sha1('default'),
            'filter_payload' => [
                'days' => 30,
                'plan' => 'all',
                'channel' => 'all',
                'portfolio' => 'all',
                'recovery_status' => 'all',
            ],
            'kpi_payload' => [
                'mrr' => 1500.00,
                'active_subscriptions' => 4,
            ],
            'drilldown_payload' => [
                'plans' => [],
                'channels' => [],
                'portfolios' => [],
                'recovery_statuses' => [],
            ],
            'snapshot_status' => 'ready',
        ];
    }
}
