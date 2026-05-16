<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\User;
use App\Models\WhiteLabelConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.dashboard', [
            'stats' => [
                'filiais' => Filial::count(),
                'usuarios' => User::count(),
                'usuarios_ativos' => User::query()->where('ativo', true)->count(),
                'white_labels' => WhiteLabelConfig::count(),
                'clientes_ativos' => Cliente::query()->where('status', 'active')->count(),
            ],
            'recentFiliais' => Filial::query()
                ->withCount('users')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    private function ensureSuperAdmin(): void
    {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-dashboard');
    }
}
