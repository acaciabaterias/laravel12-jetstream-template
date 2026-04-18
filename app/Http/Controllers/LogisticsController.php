<?php

namespace App\Http\Controllers;

use App\Models\RotaEntrega;
use Illuminate\Http\Request;

class LogisticsController extends Controller
{
    /**
     * View principal do App do Entregador (PWA shell).
     */
    public function entregadorApp(Request $request)
    {
        $user = $request->user();
        
        // Busca a rota ativa do entregador para o dia de hoje
        $rota = RotaEntrega::with(['pontos.vale.cliente', 'pontos.vale.itens.bateria'])
            ->where('entregador_id', $user->id)
            ->where('status', 'ativa')
            ->where('data_rota', now()->toDateString())
            ->first();

        return view('logistics.app', [
            'rota' => $rota,
            'user' => $user
        ]);
    }
}
