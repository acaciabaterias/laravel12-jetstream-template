<?php

namespace App\Http\Controllers;

use App\Models\BateriaEmprestimo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TermoEmprestimoController extends Controller
{
    /**
     * Gera o PDF do Termo de Responsabilidade para empréstimo de bateria (backup).
     */
    public function gerar(int $emprestimoId)
    {
        $emprestimo = BateriaEmprestimo::with(['osGarantia.cliente', 'osGarantia.filial', 'bateria'])->findOrFail($emprestimoId);

        $pdf = Pdf::loadView('pdf.termo-emprestimo', [
            'emprestimo' => $emprestimo,
            'cliente' => $emprestimo->osGarantia->cliente,
            'filial' => $emprestimo->osGarantia->filial,
            'bateria' => $emprestimo->bateria,
        ]);

        return $pdf->stream("termo-responsabilidade-os-{$emprestimo->os_garantia_id}.pdf");
    }
}
