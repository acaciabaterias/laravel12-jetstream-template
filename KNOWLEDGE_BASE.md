# Base de Conhecimento - ERP BateriaExpert

## 1. Como redefinir senha?

Na tela de login, clique em “Esqueci minha senha”, informe seu email e siga as instruções recebidas.

## 2. Como criar um novo vale?

1. Acesse o módulo de vendas
2. Clique em “Novo Vale”
3. Selecione o cliente
4. Adicione os itens
5. Confirme se haverá devolução de sucata
6. Finalize

## 3. O que acontece se o cliente não devolver a sucata?

O sistema aplica o acréscimo definido na lógica comercial para manter a política de logística reversa.

## 4. Como consultar estoque?

Acesse o módulo de estoque e pesquise pela bateria, SKU, marca ou depósito.

## 5. Como registrar uma entrega?

No fluxo de rota do entregador, selecione a parada, informe sucata coletada, pagamento recebido e confirme a entrega.

## 6. Como abrir uma garantia?

No módulo de garantias, crie uma nova OS, vincule a venda original e registre os detalhes do problema.

## 7. O que significa laudo procedente?

Significa que a garantia foi aprovada e o cliente tem direito ao atendimento conforme política da empresa.

## 8. O que significa laudo improcedente?

Significa que a reclamação não foi aprovada e pode haver cobrança de serviço, análise ou recarga.

## 9. Como cadastrar um novo usuário?

Usuários com permissão administrativa podem acessar Configurações → Usuários e criar um novo colaborador com o papel adequado.

## 10. Como alterar logo e cores do sistema?

No menu de configurações de marca ou white label, envie o logotipo e ajuste as cores da empresa.

## 11. Como funciona o backup?

O ambiente pode ter backup automático diário, com retenção definida pela operação. Em ambientes locais ou manuais, restauração pode usar `./restore.sh`.

## 12. Onde ficam os logs?

No ambiente Laravel, os logs ficam em `storage/logs/`. Em Kubernetes, use `kubectl logs` nos deployments e statefulsets.

## 13. Como saber se o sistema está saudável?

Use os health endpoints:

- ERP Core: `/up`
- Microserviços: `/api/v1/health`

## 14. Como agir quando uma tela não carrega?

- Atualize a página
- Tente novo login
- Verifique se há erro de rede
- Acione o suporte com print, horário e usuário afetado

## 15. Quando devo chamar o suporte?

Quando houver indisponibilidade, erro recorrente, problema de acesso, falha de integração ou comportamento inesperado do sistema.
