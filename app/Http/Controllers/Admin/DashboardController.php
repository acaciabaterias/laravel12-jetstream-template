<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Filial;
use App\Models\User;
use App\Models\WhiteLabelConfig;
use App\Services\Platform\PlatformCurrencyFormattingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke(PlatformCurrencyFormattingService $platformCurrencyFormattingService): View
    {
        $this->ensureSuperAdmin();
        $resolvedCurrency = config('platform_currencies.current_currency', config('platform_currencies.default_currency', 'BRL'));

        return view('admin.dashboard', [
            'stats' => [
                'filiais' => Filial::count(),
                'usuarios' => User::count(),
                'usuarios_ativos' => User::query()->where('ativo', true)->count(),
                'white_labels' => WhiteLabelConfig::count(),
                'clientes_ativos' => Cliente::query()->where('status', 'active')->count(),
                'monthly_billing' => $platformCurrencyFormattingService->formatFromBase(24700, $resolvedCurrency),
                'projected_mrr' => $platformCurrencyFormattingService->formatFromBase(31200, $resolvedCurrency),
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
