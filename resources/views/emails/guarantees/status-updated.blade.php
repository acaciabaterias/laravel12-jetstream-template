<x-mail::message>
# Atualização de garantia

A ordem de serviço **#{{ $ordemServico->id }}** teve atualização de status.

Status atual: **{{ $ordemServico->status }}**  
Resultado técnico: **{{ $ordemServico->resultado ?? 'em análise' }}**

<x-mail::button :url="$portalUrl">
Consultar atendimento
</x-mail::button>

Se houver cobrança associada, ela será exibida no painel do cliente.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
