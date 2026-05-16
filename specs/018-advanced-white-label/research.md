# Research: Módulo 018 - Advanced White Label Experience

## Decisão 1: Centralizar identidade visual e temas no banco central

- **Decision**: manter identidade visual, versões de tema e eventos de publicação no banco central.
- **Rationale**: white label é uma capacidade de plataforma e precisa de governança compartilhada, auditável e tenant-aware.
- **Alternatives considered**:
  - Persistir no banco tenant: rejeitado porque mistura branding com domínio operacional do ERP.
  - Persistir só em arquivos estáticos: rejeitado porque perde histórico, inspeção e rollback auditável.

## Decisão 2: Tratar publicação de tema como workflow explícito

- **Decision**: separar draft, publicado e revertido como estados explícitos de versão.
- **Rationale**: isso permite bloquear promoções inválidas e restaurar a última versão saudável com clareza.
- **Alternatives considered**:
  - Atualização in-place do tema ativo: rejeitado porque elimina trilha operacional.
  - Aprovação puramente manual fora do ERP: rejeitado porque quebra governança central.

## Decisão 3: Validar tokens mínimos antes da publicação

- **Decision**: exigir presença de tokens obrigatórios e validação mínima de contraste antes da promoção.
- **Rationale**: evita que branding incompleto degrade legibilidade ou áreas críticas da interface.
- **Alternatives considered**:
  - Validar apenas na renderização: rejeitado porque o erro seria percebido tarde demais.
  - Não validar contraste: rejeitado porque aumenta risco de acessibilidade e suporte reativo.

## Decisão 4: Preservar fallback seguro para shell administrativo

- **Decision**: manter referência de tema padrão e ativos fallback para áreas críticas.
- **Rationale**: a navegação administrativa não pode depender exclusivamente de branding customizado saudável.
- **Alternatives considered**:
  - Exigir completude total sem fallback: rejeitado porque indisponibilidade de um ativo quebraria a experiência.
  - Permitir ativo opcional sem padrão: rejeitado porque gera inconsistência imprevisível.
