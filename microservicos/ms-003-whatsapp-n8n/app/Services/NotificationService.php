<?php

namespace App\Services;

use App\Models\ContatoBlacklist;
use App\Models\FilaNotificacao;
use App\Models\WorkflowExecucao;
use App\Services\Drivers\EmailDriver;
use App\Services\Drivers\SmsDriver;
use App\Services\Drivers\WhatsappDriver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class NotificationService
{
    protected function resolveDriver(string $channel = 'whatsapp'): object
    {
        return match ($channel) {
            'sms' => app(SmsDriver::class),
            'email' => app(EmailDriver::class),
            default => app(WhatsappDriver::class),
        };
    }

    public function send(string $to, string $message, string $evento, array $payload = []): array
    {
        if (ContatoBlacklist::query()->where('numero_tel', $to)->exists()) {
            return ['status' => 'blocked', 'reason' => 'Blacklist'];
        }

        $now = now();
        $start = Carbon::copy($now)->setTime(8, 0);
        $end = Carbon::copy($now)->setTime(20, 0);

        if (! $now->betweenIncluded($start, $end) || $now->isSunday()) {
            $agendado = $this->nextBusinessWindow($now);

            FilaNotificacao::query()->create([
                'evento' => $evento,
                'destinatario' => $to,
                'canal' => $payload['canal'] ?? 'whatsapp',
                'payload' => $payload,
                'status' => 'pendente',
                'agendado_para' => $agendado,
            ]);

            return ['status' => 'scheduled', 'scheduled_for' => $agendado];
        }

        $channel = $payload['canal'] ?? 'whatsapp';
        $driver = $this->resolveDriver($channel);
        $result = $driver->send($to, $message, $payload);

        WorkflowExecucao::query()->create([
            'workflow_name' => $payload['workflow_name'] ?? 'manual-send',
            'evento_trigger' => $evento,
            'status' => $result['status'] ?? 'success',
            'payload_entrada' => $payload,
            'mensagem_enviada' => $message,
            'canal' => $channel,
            'destinatario' => $to,
        ]);

        return $result;
    }

    public function history(string $clienteId): Collection
    {
        return WorkflowExecucao::query()
            ->where('destinatario', $clienteId)
            ->latest('created_at')
            ->get();
    }

    public function queue(): Collection
    {
        return FilaNotificacao::query()
            ->orderBy('agendado_para')
            ->get();
    }

    public function health(): array
    {
        try {
            $status = $this->resolveDriver('whatsapp')->getStatus('health-check');
        } catch (RuntimeException) {
            $status = 'degraded';
        }

        return [
            'service' => 'ms-003-whatsapp-n8n',
            'status' => $status === 'connected' ? 'ok' : 'degraded',
            'driver' => config('services.notification.default_driver', 'whatsapp'),
            'n8n_url' => config('services.n8n.url'),
            'evolution_url' => config('services.evolution.url'),
        ];
    }

    protected function nextBusinessWindow(Carbon $now): Carbon
    {
        $candidate = Carbon::copy($now);

        if ($candidate->hour >= 20) {
            $candidate->addDay();
        }

        $candidate->setTime(8, 0);

        while ($candidate->isSunday()) {
            $candidate->addDay()->setTime(8, 0);
        }

        return $candidate;
    }
}
