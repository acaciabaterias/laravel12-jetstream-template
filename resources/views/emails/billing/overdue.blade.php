<x-mail::message>
# Assinatura em atraso

Olá, equipe **{{ $tenant->razao_social }}**.

Identificamos pendências financeiras na assinatura do tenant **{{ $tenant->subdominio }}**.

<x-mail::button :url="$billingUrl">
Regularizar cobrança
</x-mail::button>

Regularize o pagamento para evitar bloqueios operacionais.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
