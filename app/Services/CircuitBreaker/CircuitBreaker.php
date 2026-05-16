<?php

namespace App\Services\CircuitBreaker;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Spatie\Prometheus\Facades\Prometheus;

enum CircuitState: string
{
    case CLOSED = 'closed';      // Operação normal
    case OPEN = 'open';          // Falha, bloqueia chamadas
    case HALF_OPEN = 'half_open'; // Testando se recupera normal
}

class CircuitBreaker
{
    private string $serviceName;

    private int $failureThreshold = 5;      // Falhas para abrir

    private int $timeoutInSeconds = 60;      // Tempo aberto

    private int $halfOpenMaxAttempts = 3;    // Testes no half-open

    private Repository $cache;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
        // Tenta usar o store 'redis' se disponível, caso contrário cai para o padrão
        try {
            $this->cache = Cache::store('redis');
        } catch (\Exception $e) {
            $this->cache = Cache::store();
        }
    }

    public function call(callable $callback, ?callable $fallback = null): mixed
    {
        $state = $this->getState();

        if ($state === CircuitState::OPEN) {
            $this->recordRejectedCall();

            return $this->executeFallback($fallback, 'circuit_open');
        }

        try {
            $result = $callback();
            $this->recordSuccess();

            return $result;
        } catch (\Exception $e) {
            $this->recordFailure();

            return $this->executeFallback($fallback, $e->getMessage());
        }
    }

    private function getState(): CircuitState
    {
        $state = $this->cache->get("circuit:{$this->serviceName}:state");

        if ($state === CircuitState::OPEN->value) {
            $openedAt = $this->cache->get("circuit:{$this->serviceName}:opened_at");
            if ($openedAt && now()->diffInSeconds($openedAt) >= $this->timeoutInSeconds) {
                $this->transitionToHalfOpen();

                return CircuitState::HALF_OPEN;
            }

            return CircuitState::OPEN;
        }

        return CircuitState::from($state ?? CircuitState::CLOSED->value);
    }

    private function recordFailure(): void
    {
        $failures = (int) $this->cache->increment("circuit:{$this->serviceName}:failures");

        if ($failures >= $this->failureThreshold) {
            $this->transitionToOpen();
        }
    }

    private function recordSuccess(): void
    {
        $state = $this->getState();

        if ($state === CircuitState::HALF_OPEN) {
            $attempts = (int) $this->cache->increment("circuit:{$this->serviceName}:half_open_attempts");
            if ($attempts >= $this->halfOpenMaxAttempts) {
                $this->transitionToClosed();
            }
        } else {
            $this->cache->forget("circuit:{$this->serviceName}:failures");
        }
    }

    private function transitionToOpen(): void
    {
        $this->cache->put("circuit:{$this->serviceName}:state", CircuitState::OPEN->value);
        $this->cache->put("circuit:{$this->serviceName}:opened_at", now());
        $this->recordMetric('circuit_opened');
    }

    private function transitionToHalfOpen(): void
    {
        $this->cache->put("circuit:{$this->serviceName}:state", CircuitState::HALF_OPEN->value);
        $this->cache->put("circuit:{$this->serviceName}:half_open_attempts", 0);
        $this->recordMetric('circuit_half_opened');
    }

    private function transitionToClosed(): void
    {
        $this->cache->put("circuit:{$this->serviceName}:state", CircuitState::CLOSED->value);
        $this->cache->forget("circuit:{$this->serviceName}:failures");
        $this->cache->forget("circuit:{$this->serviceName}:half_open_attempts");
        $this->recordMetric('circuit_closed');
    }

    private function recordMetric(string $event): void
    {
        try {
            Prometheus::counter('circuit_breaker_events_total')
                ->label('service', $this->serviceName)
                ->label('event', $event)
                ->increment();
        } catch (\Exception $e) {
            // Ignora erros de métricas
        }
    }

    private function recordRejectedCall(): void
    {
        try {
            Prometheus::counter('circuit_breaker_rejected_calls_total')
                ->label('service', $this->serviceName)
                ->increment();
        } catch (\Exception $e) {
            // Ignora erros de métricas
        }
    }

    private function executeFallback(?callable $fallback, string $reason): mixed
    {
        try {
            Prometheus::counter('circuit_breaker_fallback_executions_total')
                ->label('service', $this->serviceName)
                ->label('reason', $reason)
                ->increment();
        } catch (\Exception $e) {
            // Ignora erros de métricas
        }

        if ($fallback) {
            return $fallback();
        }

        throw new CircuitBreakerException("Service {$this->serviceName} is unavailable. Reason: {$reason}");
    }
}
