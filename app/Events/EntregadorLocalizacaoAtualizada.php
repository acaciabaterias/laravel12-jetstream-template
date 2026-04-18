<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntregadorLocalizacaoAtualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $filialId,
        public int $entregadorId,
        public string $entregadorNome,
        public float $latitude,
        public float $longitude,
        public string $timestamp,
    ) {}

    /**
     * Presence channel scoped per filial — prevents cross-branch GPS espionage (T017).
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('filial.' . $this->filialId . '.logistica'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'gps.atualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'entregador_id' => $this->entregadorId,
            'nome' => $this->entregadorNome,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'ts' => $this->timestamp,
        ];
    }
}
