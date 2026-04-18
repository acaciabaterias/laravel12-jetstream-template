<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Termo de Responsabilidade - Empréstimo de Bateria</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; line-height: 1.6; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #1a1a1a; text-transform: uppercase; font-size: 20px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 11px; }
        .section { margin-bottom: 25px; }
        .section-title { font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #ddd; margin-bottom: 10px; font-size: 11px; color: #444; }
        .grid { display: flex; flex-wrap: wrap; }
        .col { margin-bottom: 10px; }
        .label { font-weight: bold; color: #777; font-size: 10px; text-transform: uppercase; }
        .value { color: #000; font-weight: bold; font-size: 13px; }
        .text { text-align: justify; margin-top: 40px; }
        .signature-block { margin-top: 80px; text-align: center; }
        .signature-line { width: 300px; border-top: 1px solid #000; margin: 0 auto 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Termo de Responsabilidade</h1>
        <p>{{ $filial->razao_social }} | CNPJ: {{ $filial->cnpj }}</p>
    </div>

    <div class="section">
        <div class="section-title">Dados do Cliente</div>
        <div class="col">
            <span class="label">Cliente:</span> <span class="value">{{ $cliente->razao_social }}</span><br>
            <span class="label">CNPJ/CPF:</span> <span class="value">{{ $cliente->cnpj }}</span><br>
            <span class="label">Telefone:</span> <span class="value">{{ $cliente->telefone }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dados do Bem Emprestado (Backup)</div>
        <div class="col">
            <span class="label">Bateria Técnica:</span> <span class="value">{{ $bateria->marca }} {{ $bateria->amperagem }}Ah</span><br>
            <span class="label">O.S. de Garantia Referente:</span> <span class="value">#{{ str_pad($emprestimo->os_garantia_id, 5, '0', STR_PAD_LEFT) }}</span><br>
            <span class="label">Data de Retirada:</span> <span class="value">{{ $emprestimo->data_retirada->format('d/m/Y') }}</span><br>
            <span class="label">Data de Devolução Obrigatória:</span> <span class="value" style="color: #d00;">{{ $emprestimo->data_devolucao_prevista->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="text">
        <p>Pelo presente instrumento, o CLIENTE acima identificado declara ter recebido em perfeito estado de conservação e funcionamento a bateria descrita, a título de **empréstimo provisório** (backup), enquanto sua bateria original permanece sob análise técnica em laboratório.</p>
        
        <p>O CLIENTE obriga-se a:</p>
        <ol>
            <li>Zelar pela guarda e conservação do bem como se seu fosse;</li>
            <li>Efetuar a devolução do equipamento na data supracitada, sob pena de cobrança integral do valor de mercado do produto na ausência do retorno;</li>
            <li>Não abrir, violar ou tentar reparar o equipamento emprestado sob nenhuma hipótese.</li>
        </ol>

        <p>A não devolução do equipamento no prazo estipulado autoriza a {{ $filial->nome_fantasia }} a emitir faturamento de venda automática do referido bem, utilizando os dados cadastrais do cliente.</p>
    </div>

    <div class="signature-block">
        <div class="signature-line"></div>
        <div class="value">{{ $cliente->razao_social }}</div>
        <div class="label">Assinatura do Cliente</div>
    </div>

    <div class="footer">
        Gerado pelo sistema BateriaExpert ERP em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
