<?php

namespace App\Http\Middleware;

use App\Models\Filial;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetFilialContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Se o usuário não tem uma filial na sessão ainda
            if (! $request->session()->has('filial_id')) {
                // Configurar para a primeira filial ativa do sistema
                $filialDefault = Filial::where('active', true)->first();

                if ($filialDefault) {
                    $request->session()->put('filial_id', $filialDefault->id);
                }
            }
        }

        return $next($request);
    }
}
