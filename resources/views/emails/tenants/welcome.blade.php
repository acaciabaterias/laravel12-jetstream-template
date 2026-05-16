<x-mail::message>
# Bem-vindo ao BateriaExpert

Olá, equipe **{{ $tenant->razao_social }}**.

Seu ambiente foi provisionado com sucesso e já está pronto para configuração inicial.

<x-mail::button :url="$adminUrl">
Acessar painel
</x-mail::button>

Subdomínio: **{{ $tenant->subdominio }}**  
Plano atual: **{{ $tenant->plano }}**

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
