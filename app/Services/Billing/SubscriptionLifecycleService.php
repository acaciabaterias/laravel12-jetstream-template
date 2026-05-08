<?php

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\EventoComercialAssinante;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionLifecycleService
{
    private readonly PlatformSubscriptionStateMachine $stateMachine;

    public function __construct(?PlatformSubscriptionStateMachine $stateMachine = null)
    {
        $this->stateMachine = $stateMachine ?? new PlatformSubscriptionStateMachine;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function activate(
        Cliente $cliente,
        PlanoComercial $planoComercial,
        ?PoliticaInadimplencia $politicaInadimplencia = null,
        array $attributes = [],
        ?UsuarioPlataforma $actor = null,
    ): AssinaturaPlataforma {
        return DB::connection('central')->transaction(function () use ($cliente, $planoComercial, $politicaInadimplencia, $attributes, $actor): AssinaturaPlataforma {
            $effectiveDate = Carbon::parse($attributes['data_inicio'] ?? now())->startOfDay();
            $nextCycleDate = Carbon::parse($attributes['data_proximo_ciclo'] ?? $effectiveDate->copy()->addMonth())->startOfDay();
            $targetStatus = $attributes['status'] ?? 'active';

            if (! $this->stateMachine->canTransition(null, $targetStatus)) {
                throw new InvalidArgumentException("Status inicial de assinatura invalido: {$targetStatus}");
            }

            $assinatura = AssinaturaPlataforma::query()->create([
                'cliente_id' => $cliente->id,
                'plano_id' => $planoComercial->id,
                'politica_inadimplencia_id' => $politicaInadimplencia?->id,
                'status' => $targetStatus,
                'data_inicio' => $effectiveDate->toDateString(),
                'data_proximo_ciclo' => $nextCycleDate->toDateString(),
                'data_termino' => $attributes['data_termino'] ?? null,
                'observacoes' => $attributes['observacoes'] ?? null,
                'metadata' => $attributes['metadata'] ?? ['source' => 'platform_billing'],
            ]);

            $cliente->update([
                'plano' => $planoComercial->slug,
                'plano_atual_id' => $planoComercial->id,
                'status' => $assinatura->status === 'trial' ? 'trial' : 'active',
                'subscription_ends_at' => $assinatura->data_termino,
                'billing_blocked' => false,
            ]);

            $this->logEvent(
                cliente: $cliente,
                assinatura: $assinatura,
                eventType: 'subscription_activated',
                beforeState: null,
                afterState: $this->snapshot($assinatura),
                actor: $actor,
                reason: $attributes['reason'] ?? 'Assinatura ativada.',
                effectiveAt: $effectiveDate,
            );

            $this->publishBackboneEvent(
                eventType: 'ASSINATURA_ATIVADA',
                assinaturaPlataforma: $assinatura->refresh()->loadMissing('cliente', 'plano'),
                payload: [
                    'subscription_id' => $assinatura->id,
                    'tenant_id' => $cliente->id,
                    'plan_slug' => $planoComercial->slug,
                    'status' => $assinatura->status,
                    'effective_at' => $effectiveDate->toIso8601String(),
                ],
                consumers: ['platform', 'ms-003'],
                schemaDefinition: ['subscription_id' => 'integer', 'tenant_id' => 'integer', 'plan_slug' => 'string', 'status' => 'string', 'effective_at' => 'datetime'],
            );

            return $assinatura->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function changePlan(
        AssinaturaPlataforma $assinatura,
        PlanoComercial $novoPlano,
        array $attributes = [],
        ?UsuarioPlataforma $actor = null,
    ): AssinaturaPlataforma {
        return DB::connection('central')->transaction(function () use ($assinatura, $novoPlano, $attributes, $actor): AssinaturaPlataforma {
            if (! in_array($assinatura->status, ['trial', 'active', 'grace_period', 'blocked'], true)) {
                throw new InvalidArgumentException('Nao e possivel alterar o plano de uma assinatura encerrada.');
            }

            $beforeState = $this->snapshot($assinatura);
            $effectiveDate = Carbon::parse($attributes['effective_at'] ?? now());
            $nextCycleDate = array_key_exists('data_proximo_ciclo', $attributes)
                ? Carbon::parse($attributes['data_proximo_ciclo'])->toDateString()
                : $assinatura->data_proximo_ciclo?->toDateString();

            $assinatura->update([
                'plano_id' => $novoPlano->id,
                'data_proximo_ciclo' => $nextCycleDate,
                'observacoes' => $attributes['observacoes'] ?? $assinatura->observacoes,
            ]);

            $assinatura->cliente->update([
                'plano' => $novoPlano->slug,
                'plano_atual_id' => $novoPlano->id,
            ]);

            $assinatura->refresh();

            $this->logEvent(
                cliente: $assinatura->cliente,
                assinatura: $assinatura,
                eventType: 'plan_changed',
                beforeState: $beforeState,
                afterState: $this->snapshot($assinatura),
                actor: $actor,
                reason: $attributes['reason'] ?? 'Plano alterado.',
                effectiveAt: $effectiveDate,
            );

            $this->publishBackboneEvent(
                eventType: 'PLANO_ALTERADO',
                assinaturaPlataforma: $assinatura->refresh()->loadMissing('cliente', 'plano'),
                payload: [
                    'subscription_id' => $assinatura->id,
                    'tenant_id' => $assinatura->cliente_id,
                    'previous_plan_id' => $beforeState['plano_id'] ?? null,
                    'current_plan_id' => $assinatura->plano_id,
                    'current_plan_slug' => $assinatura->plano?->slug,
                    'effective_at' => $effectiveDate->toIso8601String(),
                ],
                consumers: ['platform', 'analytics'],
                schemaDefinition: ['subscription_id' => 'integer', 'tenant_id' => 'integer', 'previous_plan_id' => 'integer|null', 'current_plan_id' => 'integer', 'current_plan_slug' => 'string|null', 'effective_at' => 'datetime'],
            );

            return $assinatura;
        });
    }

    public function cancel(
        AssinaturaPlataforma $assinatura,
        string $reason,
        ?UsuarioPlataforma $actor = null,
        ?Carbon $effectiveAt = null,
    ): AssinaturaPlataforma {
        return DB::connection('central')->transaction(function () use ($assinatura, $reason, $actor, $effectiveAt): AssinaturaPlataforma {
            if (! $this->stateMachine->canTransition($assinatura->status, 'cancelled')) {
                throw new InvalidArgumentException('A assinatura nao pode ser cancelada a partir do estado atual.');
            }

            $beforeState = $this->snapshot($assinatura);
            $effectiveDate = ($effectiveAt ?? now())->startOfDay();

            $assinatura->update([
                'status' => 'cancelled',
                'data_termino' => $effectiveDate->toDateString(),
                'cancel_reason' => $reason,
            ]);

            $assinatura->cliente->update([
                'status' => 'cancelled',
                'subscription_ends_at' => $effectiveDate->toDateString(),
            ]);

            $assinatura->refresh();

            $this->logEvent(
                cliente: $assinatura->cliente,
                assinatura: $assinatura,
                eventType: 'subscription_cancelled',
                beforeState: $beforeState,
                afterState: $this->snapshot($assinatura),
                actor: $actor,
                reason: $reason,
                effectiveAt: $effectiveDate,
            );

            $this->publishBackboneEvent(
                eventType: 'ASSINATURA_CANCELADA',
                assinaturaPlataforma: $assinatura->refresh()->loadMissing('cliente', 'plano'),
                payload: [
                    'subscription_id' => $assinatura->id,
                    'tenant_id' => $assinatura->cliente_id,
                    'status' => $assinatura->status,
                    'cancel_reason' => $reason,
                    'effective_at' => $effectiveDate->toIso8601String(),
                ],
                consumers: ['platform', 'analytics'],
                schemaDefinition: ['subscription_id' => 'integer', 'tenant_id' => 'integer', 'status' => 'string', 'cancel_reason' => 'string', 'effective_at' => 'datetime'],
            );

            return $assinatura;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(AssinaturaPlataforma $assinatura): array
    {
        return [
            'status' => $assinatura->status,
            'plano_id' => $assinatura->plano_id,
            'data_inicio' => $assinatura->data_inicio?->toDateString(),
            'data_proximo_ciclo' => $assinatura->data_proximo_ciclo?->toDateString(),
            'data_termino' => $assinatura->data_termino?->toDateString(),
            'cancel_reason' => $assinatura->cancel_reason,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $beforeState
     * @param  array<string, mixed>|null  $afterState
     */
    private function logEvent(
        Cliente $cliente,
        AssinaturaPlataforma $assinatura,
        string $eventType,
        ?array $beforeState,
        ?array $afterState,
        ?UsuarioPlataforma $actor,
        string $reason,
        Carbon $effectiveAt,
    ): EventoComercialAssinante {
        return EventoComercialAssinante::query()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $assinatura->id,
            'actor_user_id' => $actor?->id,
            'event_type' => $eventType,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'effective_at' => $effectiveAt,
            'reason' => $reason,
            'metadata' => [
                'plano_slug' => $assinatura->plano?->slug,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $consumers
     * @param  array<string, mixed>  $schemaDefinition
     */
    private function publishBackboneEvent(
        string $eventType,
        AssinaturaPlataforma $assinaturaPlataforma,
        array $payload,
        array $consumers,
        array $schemaDefinition,
    ): void {
        app(PlatformBillingEventPublisher::class)->publish(
            eventType: $eventType,
            assinaturaPlataforma: $assinaturaPlataforma,
            payload: $payload,
            consumers: $consumers,
            schemaDefinition: $schemaDefinition,
        );
    }
}
