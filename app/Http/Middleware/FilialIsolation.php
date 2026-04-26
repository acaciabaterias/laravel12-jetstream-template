<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilialIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Super admin tem acesso irrestrito
        if ($user && $user->papel === 'super_admin') {
            return $next($request);
        }

        // Usuário comum: filial_id obrigatório
        if (! $user || ! $user->filial_id) {
            abort(403, 'Usuário não associado a nenhuma filial');
        }

        // Força o contexto da filial na sessão
        session(['filial_id' => $user->filial_id]);

        // Verifica acesso a recursos de outras filiais
        $rotaFilialId = $request->route('filial_id') ?? $request->input('filial_id');
        if ($rotaFilialId && $rotaFilialId != $user->filial_id) {
            abort(403, 'Acesso negado: você não pertence a esta filial');
        }

        return $next($request);
    }
}
