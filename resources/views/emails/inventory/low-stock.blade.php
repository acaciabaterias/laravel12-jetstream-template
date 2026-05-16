<x-mail::message>
# Alerta de estoque baixo

Identificamos que o item **{{ $bateria->sku }} - {{ $bateria->marca }}** atingiu nível crítico de saldo.

Saldo atual: **{{ $saldoAtual }} unidades**

<x-mail::button :url="$inventoryUrl">
Revisar estoque
</x-mail::button>

Se necessário, antecipe compras e reabastecimento para evitar ruptura.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
