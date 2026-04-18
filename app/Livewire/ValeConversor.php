<?php

namespace App\Livewire;

use App\Jobs\ConvertValeToOsJob;
use App\Jobs\ConvertValeToPedidoJob;
use Exception;
use Livewire\Attributes\On;
use Livewire\Component;

class ValeConversor extends Component
{
    #[On('openConverseVale')]
    public function openConverseVale($payload)
    {
        $type = $payload['type'] ?? '';
        $valeId = $payload['valeId'] ?? null;

        if (! $valeId) {
            $this->addError('geral', 'ID de Vale inválido.');

            return;
        }

        try {
            if ($type === 'pedido') {
                // Dispatch synchronously to show immediate UI response
                ConvertValeToPedidoJob::dispatchSync(
                    $valeId,
                    auth()->id() ?? 1,
                    auth()->user()->filial_id ?? session('filial_id')
                );
                session()->flash('success', 'Pedido gerado e estoque baixado definitivamente com sucesso!');
                $this->redirectRoute('dashboard');

                return;
            }

            if ($type === 'os') {
                ConvertValeToOsJob::dispatchSync(
                    $valeId,
                    auth()->id() ?? 1,
                    auth()->user()->filial_id ?? session('filial_id')
                );
                session()->flash('success', 'Ordem de Serviço criada. O estoque foi retido internamente na Oficina.');
                $this->redirectRoute('dashboard');

                return;
            }

        } catch (Exception $e) {
            session()->flash('error', 'Falha ao processar operação: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.vale-conversor');
    }
}
