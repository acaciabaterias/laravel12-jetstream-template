# Quickstart: Módulo 020 - Advanced Revenue Recovery Automation

## Objetivo

Validar localmente a automação avançada de recuperação sobre a base já consolidada dos módulos `010`, `011`, `012`, `013` e `019`, cobrindo publicação controlada de política, dispatch adaptativo, detecção de violação e rollback.

## Pré-requisitos

- banco central configurado
- módulos `011` a `013` operacionais com casos e ações de recovery disponíveis
- backbone `010` operacional
- visibilidade executiva do módulo `019` já disponível
- permissões super admin e billing válidas

## Sequência sugerida

1. Acessar o painel central de recovery automation com usuário autorizado.
2. Publicar uma política automatizada controlada para um segmento reduzido.
3. Criar ou reaproveitar casos elegíveis da régua de recuperação.
4. Validar a definição da próxima ação automática com fallback e supressão.
5. Confirmar distribuição de variante ou holdout para o segmento configurado.
6. Induzir uma violação controlada ou degradação observável.
7. Executar rollback para a última política saudável.
8. Confirmar publicação dos eventos materiais no backbone `010`.

## Cenários de validação

- Publicação de política sem guardrail deve ser bloqueada.
- Dispatch adaptativo deve respeitar fallback, promessa e cooldown.
- Grupo de controle não pode receber a mesma automação da variante ativa.
- Violação material deve aparecer em inspeção reutilizável.
- Rollback deve preservar evidência da política revertida e da política restaurada.

## Critérios para avançar à implementação completa

- política automatizada versionada e publicável com escopo controlado
- jornadas adaptativas com dispatch idempotente e fallback seguro
- experimento ou holdout auditável por caso tratado
- violação material e rollback expostos em painel e inspeção
- runbook operacional cobrindo publicação, violação e reversão
