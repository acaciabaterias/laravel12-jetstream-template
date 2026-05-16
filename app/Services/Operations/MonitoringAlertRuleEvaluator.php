<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\AlertRuleDefinition;

class MonitoringAlertRuleEvaluator
{
    /**
     * @param  array{
     *     flow_name?:string,
     *     target_name?:string,
     *     environment?:string,
     *     latency_ms?:int,
     *     sample_count?:int,
     *     collector_unavailable?:bool,
     *     scrape_status?:string|null
     * }  $context
     * @return array{
     *     triggered:bool,
     *     reason:string,
     *     actual:mixed,
     *     metric:string,
     *     operator:string
     * }
     */
    public function evaluate(AlertRuleDefinition $rule, array $context): array
    {
        $metric = (string) data_get($rule->metadata, 'metric', config('monitoring_consolidation.alerts.default_metric', 'latency_ms'));
        $operator = strtolower((string) data_get($rule->metadata, 'operator', config('monitoring_consolidation.alerts.default_operator', 'gte')));
        $threshold = data_get($rule->metadata, 'threshold');
        $actual = $this->resolveActualValue($metric, $context);
        $triggered = $this->matches($operator, $actual, $threshold);

        return [
            'triggered' => $triggered,
            'reason' => $triggered
                ? sprintf('%s %s %s', $metric, $operator, $this->stringify($threshold))
                : sprintf('%s within expected threshold', $metric),
            'actual' => $actual,
            'metric' => $metric,
            'operator' => $operator,
        ];
    }

    private function resolveActualValue(string $metric, array $context): mixed
    {
        return match ($metric) {
            'collector_unavailable' => (bool) ($context['collector_unavailable'] ?? false),
            'scrape_status' => (string) ($context['scrape_status'] ?? ''),
            'sample_count' => (int) ($context['sample_count'] ?? 0),
            default => (int) ($context['latency_ms'] ?? 0),
        };
    }

    private function matches(string $operator, mixed $actual, mixed $threshold): bool
    {
        return match ($operator) {
            'gt' => $actual > $threshold,
            'gte' => $actual >= $threshold,
            'lt' => $actual < $threshold,
            'lte' => $actual <= $threshold,
            'eq' => $actual === $threshold,
            'neq' => $actual !== $threshold,
            'in' => in_array($actual, is_array($threshold) ? $threshold : [$threshold], true),
            default => false,
        };
    }

    private function stringify(mixed $value): string
    {
        if (is_array($value)) {
            return implode(',', array_map(fn (mixed $entry): string => (string) $entry, $value));
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
