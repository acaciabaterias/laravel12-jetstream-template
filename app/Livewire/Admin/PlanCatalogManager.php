<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\PlanoComercial;
use App\Services\Billing\PlanCatalogService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PlanCatalogManager extends Component
{
    public string $nome = '';

    public string $slug = '';

    public string $precoMensal = '0.00';

    public string $precoAnual = '';

    public string $periodicidade = 'mensal';

    public int $maxUsuarios = 3;

    public int $maxEstoqueItens = 500;

    public bool $hasWhiteLabel = false;

    public bool $hasSupportPriority = false;

    public bool $ativo = true;

    public function updatedNome(string $value): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($value);
        }
    }

    public function save(PlanCatalogService $planCatalogService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-billing');

        $validated = $this->validate(
            $this->rules(),
            $this->messages(),
        );

        $planCatalogService->create([
            'nome' => $validated['nome'],
            'slug' => $validated['slug'],
            'preco_mensal' => $validated['precoMensal'],
            'preco_anual' => $validated['precoAnual'] !== '' ? $validated['precoAnual'] : null,
            'periodicidade' => $validated['periodicidade'],
            'max_usuarios' => $validated['maxUsuarios'],
            'max_estoque_itens' => $validated['maxEstoqueItens'],
            'has_white_label' => $validated['hasWhiteLabel'],
            'has_support_priority' => $validated['hasSupportPriority'],
            'ativo' => $validated['ativo'],
        ]);

        $this->resetForm();
        session()->flash('status', 'Plano comercial criado com sucesso.');
    }

    public function render()
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-billing');

        return view('livewire.admin.plan-catalog-manager', [
            'plans' => PlanoComercial::query()->orderBy('preco_mensal')->get(),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique(PlanoComercial::class, 'slug')],
            'precoMensal' => ['required', 'numeric', 'min:0'],
            'precoAnual' => ['nullable', 'numeric', 'min:0'],
            'periodicidade' => ['required', Rule::in(['mensal', 'trimestral', 'anual', 'custom'])],
            'maxUsuarios' => ['required', 'integer', 'min:1'],
            'maxEstoqueItens' => ['required', 'integer', 'min:1'],
            'hasWhiteLabel' => ['boolean'],
            'hasSupportPriority' => ['boolean'],
            'ativo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome comercial do plano.',
            'slug.unique' => 'Já existe um plano com este identificador.',
            'precoMensal.required' => 'Informe o valor mensal do plano.',
            'periodicidade.required' => 'Informe a periodicidade do plano.',
        ];
    }

    private function resetForm(): void
    {
        $this->nome = '';
        $this->slug = '';
        $this->precoMensal = '0.00';
        $this->precoAnual = '';
        $this->periodicidade = 'mensal';
        $this->maxUsuarios = 3;
        $this->maxEstoqueItens = 500;
        $this->hasWhiteLabel = false;
        $this->hasSupportPriority = false;
        $this->ativo = true;
    }
}
