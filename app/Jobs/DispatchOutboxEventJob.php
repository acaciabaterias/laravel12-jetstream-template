<?php

namespace App\Jobs;

use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Services\Integration\IntegrationMetrics;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DispatchOutboxEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $eventoOutboxId) {}

    public function handle(IntegrationMetrics $metrics): void
    {
        $event = EventoOutbox::query()->findOrFail($this->eventoOutboxId);

        if (in_array($event->status, [IntegrationFlowStatus::Processed, IntegrationFlowStatus::DeadLetter], true)) {
            return;
        }

        $startedAt = now();
        $attemptNumber = $event->attempts + 1;
        $metadata = is_array($event->metadata) ? $event->metadata : [];
        $transportKind = IntegrationTransportKind::tryFrom((string) ($metadata['transport_kind'] ?? 'broker'))
            ?? IntegrationTransportKind::Broker;
        $target = (string) ($metadata['target'] ?? 'broker:default');
        $shouldFail = (bool) ($metadata['simulate_failure'] ?? false);

        $event->update([
            'status' => IntegrationFlowStatus::Processing,
            'attempts' => $attemptNumber,
        ]);

        try {
            if ($shouldFail) {
                throw new RuntimeException((string) ($metadata['failure_message'] ?? 'Falha simulada de dispatch.'));
            }

            $finishedAt = now();
            $latencyMs = (int) $startedAt->diffInMilliseconds($finishedAt);

            $delivery = EntregaIntegracao::query()->create([
                'entregavel_type' => EventoOutbox::class,
                'entregavel_id' => $event->id,
                'direction' => IntegrationDirection::Outbound,
                'transport_kind' => $transportKind,
                'target' => $target,
                'status' => IntegrationFlowStatus::Processed,
                'attempt_number' => $attemptNumber,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'latency_ms' => $latencyMs,
                'metadata' => [
                    'correlation_id' => $event->correlation_id,
                ],
            ]);

            $event->update([
                'status' => IntegrationFlowStatus::Processed,
                'dispatched_at' => $finishedAt,
                'last_error' => null,
            ]);

            $metrics->recordEvent(IntegrationDirection::Outbound, $event->event_type, IntegrationFlowStatus::Processed);
            $metrics->recordLatency(IntegrationDirection::Outbound, $target, $latencyMs);
            $metrics->syncOperationalSnapshot();
        } catch (\Throwable $exception) {
            $maxAttempts = (int) config('services.integration_backbone.retry.max_attempts', 5);
            $backoffSeconds = config('services.integration_backbone.retry.backoff_seconds', [30, 120, 600, 1800, 3600]);
            $retryIndex = max(0, min($attemptNumber - 1, count($backoffSeconds) - 1));
            $nextAvailableAt = now()->addSeconds((int) ($backoffSeconds[$retryIndex] ?? 3600));
            $nextStatus = $attemptNumber >= $maxAttempts
                ? IntegrationFlowStatus::DeadLetter
                : IntegrationFlowStatus::Pending;

            $delivery = EntregaIntegracao::query()->create([
                'entregavel_type' => EventoOutbox::class,
                'entregavel_id' => $event->id,
                'direction' => IntegrationDirection::Outbound,
                'transport_kind' => $transportKind,
                'target' => $target,
                'status' => $nextStatus === IntegrationFlowStatus::DeadLetter
                    ? IntegrationFlowStatus::DeadLetter
                    : IntegrationFlowStatus::Failed,
                'attempt_number' => $attemptNumber,
                'started_at' => $startedAt,
                'finished_at' => now(),
                'error_message' => $exception->getMessage(),
                'metadata' => [
                    'correlation_id' => $event->correlation_id,
                ],
            ]);

            $event->update([
                'status' => $nextStatus,
                'available_at' => $nextStatus === IntegrationFlowStatus::DeadLetter ? null : $nextAvailableAt,
                'last_error' => $exception->getMessage(),
            ]);

            $metrics->recordEvent(IntegrationDirection::Outbound, $event->event_type, $nextStatus);
            $metrics->syncOperationalSnapshot();

            if ($nextStatus === IntegrationFlowStatus::DeadLetter) {
                $this->recordDeadLetterAudit($event, $delivery, $exception->getMessage());
            }
        }
    }

    private function recordDeadLetterAudit(EventoOutbox $event, EntregaIntegracao $delivery, string $errorMessage): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        LogAuditJob::dispatchSync([
            'user_id' => null,
            'action' => 'dead_letter',
            'table_name' => 'entregas_integracao',
            'record_id' => $delivery->id,
            'old_values' => [
                'outbox_status' => IntegrationFlowStatus::Processing->value,
                'attempts' => max(0, $event->attempts - 1),
            ],
            'new_values' => [
                'outbox_id' => $event->id,
                'outbox_status' => $event->status->value,
                'delivery_status' => $delivery->status->value,
                'attempts' => $event->attempts,
                'event_type' => $event->event_type,
                'target' => $delivery->target,
                'error_message' => $errorMessage,
            ],
        ]);
    }
}
