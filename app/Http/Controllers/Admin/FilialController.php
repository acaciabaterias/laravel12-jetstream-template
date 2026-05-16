<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFilialRequest;
use App\Http\Requests\Admin\UpdateFilialRequest;
use App\Models\Filial;
use App\Models\WhiteLabelConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class FilialController extends Controller
{
    public function index(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.filiais.index', [
            'filiais' => Filial::query()
                ->withCount('users')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.filiais.create', [
            'brandingConfig' => WhiteLabelConfig::query()->latest()->first(),
        ]);
    }

    public function store(StoreFilialRequest $request): RedirectResponse
    {
        Filial::query()->create($request->validated());

        return redirect()
            ->route('admin.filiais.index')
            ->with('status', 'Filial criada com sucesso.');
    }

    public function edit(Filial $filial): View
    {
        $this->ensureSuperAdmin();

        return view('admin.filiais.edit', [
            'filial' => $filial,
        ]);
    }

    public function update(UpdateFilialRequest $request, Filial $filial): RedirectResponse
    {
        $filial->update($request->validated());

        return redirect()
            ->route('admin.filiais.index')
            ->with('status', 'Filial atualizada com sucesso.');
    }

    public function destroy(Filial $filial): RedirectResponse
    {
        $this->ensureSuperAdmin();

        if ($filial->users()->exists()) {
            return redirect()
                ->route('admin.filiais.index')
                ->with('error', 'Não é possível excluir uma filial com usuários vinculados.');
        }

        $filial->delete();

        return redirect()
            ->route('admin.filiais.index')
            ->with('status', 'Filial removida com sucesso.');
    }

    private function ensureSuperAdmin(): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-tenants');
    }
}
