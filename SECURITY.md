# Security Policy

## Escopo

Esta politica cobre:

- ERP Core
- microservicos `MS-001` a `MS-005`
- scripts operacionais e artefatos de deploy versionados no repositorio
- integracoes documentadas em `openapi.yaml` e guias tecnicos

## O Que Deve Ser Reportado

Reporte de forma privada qualquer problema envolvendo:

- bypass de autenticacao ou autorizacao
- vazamento cross-tenant
- falhas em `database-per-client` ou RLS
- exposicao de segredos, tokens, credenciais ou dados sensiveis
- SQL injection, RCE, SSRF, path traversal ou escalacao de privilegio
- falhas em integrações fiscal, bancaria, Open Finance, WhatsApp ou geocoding
- configuracoes inseguras de Docker, Redis, PostgreSQL ou deploy

## Como Reportar

Nao abra issue publica para vulnerabilidades.

Use um canal privado de manutencao do projeto e envie:

- titulo resumido da vulnerabilidade
- descricao tecnica
- impacto esperado
- passos de reproducao
- ambiente afetado
- evidencias tecnicas
- sugestao de mitigacao, se houver

Se a sua organizacao utilizar um e-mail dedicado de seguranca, substitua este fluxo por esse contato oficial antes de expor o projeto externamente.

## O Que Nao Fazer

- nao publique PoC ou detalhes completos antes da correcao
- nao rode testes destrutivos em producao
- nao acesse ou exfiltre dados reais de clientes
- nao automatize exploracao agressiva contra ambientes operacionais

## Janela Esperada de Resposta

- triagem inicial: ate 3 dias uteis
- confirmacao ou descarte: ate 5 dias uteis
- plano de mitigacao: conforme criticidade e impacto operacional

## Boas Praticas Para Pesquisadores

- prefira prova de conceito minima
- limite o teste ao necessario para demonstrar o problema
- preserve logs e evidencias tecnicas
- indique pre-condicoes e impactos com clareza

## Fora de Escopo

Em geral, nao entram como vulnerabilidade:

- falhas puramente locais em ambiente nao endurecido
- problemas que dependem exclusivamente de segredo ja comprometido fora do sistema
- alertas teoricos sem caminho plausivel de exploracao
- sugestoes genericas sem reproducao ou impacto demonstravel

## Divulgacao Responsavel

Depois da correcao, o projeto pode divulgar:

- resumo tecnico
- impacto
- versoes afetadas
- versao corrigida
- orientacoes de upgrade ou mitigacao

## Relacao com Outras Politicas

- conduta da comunidade: [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)
- suporte geral: [SUPPORT.md](./SUPPORT.md)
- contribuicao: [CONTRIBUTING.md](./CONTRIBUTING.md)
