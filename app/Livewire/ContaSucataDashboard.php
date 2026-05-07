<?php

namespace App\Livewire;

use App\Models\Bateria;
use App\Models\ContaSucataMovimentacao;
use App\Models\Fornecedor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ContaSucataDashboard extends Component
{
    public string $entidadeTipo = 'bateria';

    public ?int $entidadeId = null;

    public ?int $bateriaId = null;

    public string $tipoMovimento = 'credito';

    public string $quantidadeKg = '1';

    public string $valorUnitario = '0';

    public string $origem = 'ajuste_manual';

    public function mount(): void
    {
        Gate::authorize('acesso-estoque');
    }

    public function registrarMovimento(): void
    {
        Gate::authorize('acesso-estoque');

        $entidadeId = $this->entidadeId ?? $this->bateriaId;

        $validated = $this->validate([
            'entidadeTipo' => ['required', 'in:bateria,cliente,fornecedor'],
            'entidadeId' => ['nullable', 'integer'],
            'tipoMovimento' => ['required', 'in:credito,debito'],
            'quantidadeKg' => ['required', 'numeric', 'min:0.01'],
            'valorUnitario' => ['required', 'numeric', 'min:0'],
            'origem' => ['required', 'string', 'max:100'],
            'bateriaId' => ['nullable', 'integer'],
        ]);

        $this->validarEntidade((string) $validated['entidadeTipo'], $entidadeId);

        $valorMovimento = round((float) $validated['quantidadeKg'] * (float) $validated['valorUnitario'], 2);
        $saldoAnterior = (float) (ContaSucataMovimentacao::query()
            ->where('entidade_tipo', $this->resolverClasseEntidade((string) $validated['entidadeTipo']))
            ->where('entidade_id', $entidadeId)
            ->latest('id')
            ->value('saldo_resultante') ?? 0);
        $saldoResultante = $validated['tipoMovimento'] === 'credito'
            ? $saldoAnterior + $valorMovimento
            : $saldoAnterior - $valorMovimento;

        ContaSucataMovimentacao::query()->create([
            'entidade_tipo' => $this->resolverClasseEntidade((string) $validated['entidadeTipo']),
            'entidade_id' => $entidadeId,
            'tipo_movimento' => $validated['tipoMovimento'],
            'quantidade_kg' => $validated['quantidadeKg'],
            'valor_unitario' => $validated['valorUnitario'],
            'saldo_resultante' => $saldoResultante,
            'origem' => $validated['origem'],
        ]);

        $this->reset(['bateriaId', 'entidadeId']);
        $this->entidadeTipo = 'bateria';
        $this->tipoMovimento = 'credito';
        $this->quantidadeKg = '1';
        $this->valorUnitario = '0';
        $this->origem = 'ajuste_manual';

        $this->dispatch('inventory-updated');
    }

    public function render()
    {
        $movimentacoes = ContaSucataMovimentacao::query()
            ->latest('id')
            ->limit(6)
            ->get();

        return view('livewire.conta-sucata-dashboard', [
            'baterias' => Bateria::query()->orderBy('sku')->get(),
            'clientes' => $this->listarClientes(),
            'fornecedores' => $this->listarFornecedores(),
            'movimentacoes' => $movimentacoes,
            'saldoAtual' => (float) ($movimentacoes->first()?->saldo_resultante ?? 0),
        ]);
    }

    protected function validarEntidade(string $tipo, ?int $entidadeId): void
    {
        if (! $entidadeId) {
            throw ValidationException::withMessages([
                'entidadeId' => 'Selecione a entidade da conta sucata.',
            ]);
        }

        $classe = $this->resolverClasseEntidade($tipo);

        $exists = match ($classe) {
            Bateria::class => Bateria::query()->whereKey($entidadeId)->exists(),
            Fornecedor::class => Schema::hasTable('fornecedores') ? Fornecedor::query()->whereKey($entidadeId)->exists() : false,
            'tenant_cliente' => DB::table('clientes')->where('id', $entidadeId)->exists(),
            default => false,
        };

        if (! $exists) {
            throw ValidationException::withMessages([
                'entidadeId' => 'Entidade selecionada é inválida.',
            ]);
        }
    }

    protected function resolverClasseEntidade(string $tipo): string
    {
        return match ($tipo) {
            'bateria' => Bateria::class,
            'fornecedor' => Schema::hasTable('fornecedores') ? Fornecedor::class : Bateria::class,
            'cliente' => 'tenant_cliente',
        };
    }

    protected function listarClientes(): Collection
    {
        $nomeColumn = Schema::hasColumn('clientes', 'nome') ? 'nome' : 'razao_social';

        return DB::table('clientes')
            ->select('id', DB::raw($nomeColumn.' as nome'))
            ->orderBy($nomeColumn)
            ->limit(100)
            ->get();
    }

    protected function listarFornecedores(): Collection
    {
        if (! Schema::hasTable('fornecedores')) {
            return collect();
        }

        return Fornecedor::query()->where('ativo', true)->orderBy('nome')->get();
    }
}
