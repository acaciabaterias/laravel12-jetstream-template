<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\Deposito;
use App\Models\ItemVale;
use App\Models\Vale;
use App\Services\ReservaEstoqueService;
use Exception;
use Livewire\Component;

class PdvManager extends Component
{
    public $valeId;

    public $clienteId;

    public $depositoId;

    public $observacoes;

    public $searchSku = '';

    public $bateriasEncontradas = [];

    // Tying dynamic logic
    public $totalGeral = 0;

    protected $rules = [
        'clienteId' => 'required|exists:clientes,id',
        'depositoId' => 'required|exists:depositos,id',
    ];

    public function mount($valeId = null)
    {
        if ($valeId) {
            $this->loadVale($valeId);
        } else {
            // Pick default deposit
            $dep = Deposito::where('filial_id', auth()->user()->filial_id ?? session('filial_id'))->first();
            if ($dep) {
                $this->depositoId = $dep->id;
            }
        }
    }

    public function loadVale($id)
    {
        $vale = Vale::findOrFail($id);
        $this->valeId = $vale->id;
        $this->clienteId = $vale->cliente_id;
        $this->observacoes = $vale->observacoes;

        $this->calcularTotal();
    }

    public function updatedClienteId()
    {
        if (! $this->valeId && $this->clienteId) {
            $this->iniciarVale();
        }
    }

    public function iniciarVale()
    {
        $this->validateOnly('clienteId');

        $vale = Vale::create([
            'cliente_id' => $this->clienteId,
            'vendedor_id' => auth()->id(),
            'filial_id' => auth()->user()->filial_id ?? session('filial_id'),
            'status' => 'aberto',
        ]);

        $this->valeId = $vale->id;
    }

    public function updatedSearchSku()
    {
        if (strlen($this->searchSku) >= 2) {
            $this->bateriasEncontradas = Bateria::where('sku', 'like', '%'.$this->searchSku.'%')
                ->orWhere('marca', 'like', '%'.$this->searchSku.'%')
                ->take(5)->get();
        } else {
            $this->bateriasEncontradas = [];
        }
    }

    public function adicionarItem($bateriaId, ReservaEstoqueService $reservaService)
    {
        if (! $this->valeId) {
            $this->addError('geral', 'Inicie o vale selecionando um cliente primeiro.');

            return;
        }

        $bateria = Bateria::findOrFail($bateriaId);
        $qtd = 1;

        // "Net Price" logic initialization
        $precoOriginal = $bateria->preco_venda ?? 0;
        $precoFinal = $precoOriginal; // Assume devolução de sucata na adição

        try {
            // Dispara integração com Modulo 004 transacionalmente bloqueante
            $reservaService->reservar(
                $bateria->id,
                $this->depositoId,
                $qtd,
                auth()->user()->filial_id ?? session('filial_id'),
                auth()->id(),
                $this->valeId
            );

            ItemVale::create([
                'vale_id' => $this->valeId,
                'bateria_id' => $bateria->id,
                'quantidade' => $qtd,
                'preco_unitario_original' => $precoOriginal,
                'preco_unitario_final' => $precoFinal,
                'flag_devolveu_sucata' => true,
            ]);

            $this->searchSku = '';
            $this->bateriasEncontradas = [];
            $this->calcularTotal();
            session()->flash('success', 'Item adicionado e estoque reservado!');

        } catch (Exception $e) {
            $this->addError('geral', $e->getMessage());
        }
    }

    public function toggleSucata($itemId)
    {
        $item = ItemVale::findOrFail($itemId);
        $bateria = $item->bateria;

        // Inverte seleção de sucata
        $item->flag_devolveu_sucata = ! $item->flag_devolveu_sucata;

        // Recalcular Net Price FR-VENDA-02
        if ($item->flag_devolveu_sucata) {
            // Se devolveu casco, paga o preco base
            $item->preco_unitario_final = $item->preco_unitario_original;
        } else {
            // Se não devolveu casco, cobra acréscimo dinâmico baseado nos parâmetros ecológicos
            $acrescimo = 0;
            if ($bateria->tem_logistica_reversa && $bateria->peso_sucata_kg && $bateria->valor_base_sucata_kg) {
                $acrescimo = $bateria->peso_sucata_kg * $bateria->valor_base_sucata_kg;
            }
            $item->preco_unitario_final = $item->preco_unitario_original + $acrescimo;
        }

        $item->save();
        $this->calcularTotal();
    }

    public function removerItem($itemId, ReservaEstoqueService $reservaService)
    {
        $item = ItemVale::findOrFail($itemId);

        try {
            $reservaService->estornar(
                $item->bateria_id,
                $this->depositoId,
                $item->quantidade,
                auth()->user()->filial_id ?? session('filial_id'),
                auth()->id(),
                $this->valeId
            );

            $item->delete();
            $this->calcularTotal();
            session()->flash('success', 'Item removido e reserva estornada integralmente.');

        } catch (Exception $e) {
            $this->addError('geral', 'Falha ao estornar: '.$e->getMessage());
        }
    }

    public function cancelarVale(ReservaEstoqueService $reservaService)
    {
        if (! $this->valeId) {
            return;
        }

        $vale = Vale::with('itens')->findOrFail($this->valeId);

        foreach ($vale->itens as $item) {
            $reservaService->estornar(
                $item->bateria_id,
                $this->depositoId,
                $item->quantidade,
                auth()->user()->filial_id ?? session('filial_id'),
                auth()->id(),
                $this->valeId
            );
        }

        $vale->status = 'cancelado';
        $vale->save();

        session()->flash('success', 'Vale cancelado e estoques liberados.');

        return redirect()->route('dashboard');
    }

    public function calcularTotal()
    {
        if (! $this->valeId) {
            return;
        }

        $this->totalGeral = ItemVale::where('vale_id', $this->valeId)
            ->sum(\Illuminate\Support\Facades\DB::raw('quantidade * preco_unitario_final'));
    }

    public function render()
    {
        $vale = null;
        if ($this->valeId) {
            $vale = Vale::with(['itens.bateria'])->find($this->valeId);
        }

        return view('livewire.pdv-manager', [
            'clientes' => Cliente::orderBy('nome')->get(),
            'depositos' => Deposito::all(),
            'vale' => $vale,
        ]);
    }
}
