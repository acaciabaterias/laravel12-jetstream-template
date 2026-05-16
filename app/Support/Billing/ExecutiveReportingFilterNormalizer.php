<?php

declare(strict_types=1);

namespace App\Support\Billing;

use Carbon\Carbon;

class ExecutiveReportingFilterNormalizer
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}
     */
    public function normalize(array $filters = []): array
    {
        $days = max(7, min(365, (int) ($filters['days'] ?? 30)));
        $periodEnd = isset($filters['period_end'])
            ? Carbon::parse((string) $filters['period_end'])->startOfDay()
            : now()->startOfDay();
        $periodStart = isset($filters['period_start'])
            ? Carbon::parse((string) $filters['period_start'])->startOfDay()
            : $periodEnd->copy()->subDays($days - 1);

        if ($periodStart->greaterThan($periodEnd)) {
            $periodStart = $periodEnd->copy()->subDays($days - 1);
        }

        return [
            'days' => $days,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'plan' => $this->normalizeToken($filters['plan'] ?? 'all'),
            'channel' => $this->normalizeToken($filters['channel'] ?? 'all'),
            'portfolio' => $this->normalizeToken($filters['portfolio'] ?? 'all'),
            'recovery_status' => $this->normalizeToken($filters['recovery_status'] ?? 'all'),
        ];
    }

    private function normalizeToken(mixed $value): string
    {
        $token = trim((string) $value);

        return $token === '' ? 'all' : strtolower($token);
    }
}
