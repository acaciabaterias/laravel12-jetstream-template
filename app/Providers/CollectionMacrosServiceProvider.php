<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class CollectionMacrosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Collection::macro('toMoney', function (string $field = 'valor', string $currency = 'R$'): string {
            /** @var Collection $this */
            $total = $this->sum(function (mixed $item) use ($field): float {
                if (is_array($item)) {
                    return (float) ($item[$field] ?? 0);
                }

                if (is_object($item)) {
                    return (float) (data_get($item, $field, 0) ?? 0);
                }

                return 0.0;
            });

            return format_money_br($total, $currency);
        });

        Collection::macro('groupByDay', function (string $field = 'created_at', string $format = 'Y-m-d'): Collection {
            /** @var Collection $this */
            return $this->groupBy(function (mixed $item) use ($field, $format): string {
                $value = is_array($item) ? ($item[$field] ?? null) : data_get($item, $field);

                if ($value instanceof \DateTimeInterface) {
                    return $value->format($format);
                }

                if (is_string($value) && $value !== '') {
                    return now()->parse($value)->format($format);
                }

                return 'sem-data';
            });
        });
    }
}
