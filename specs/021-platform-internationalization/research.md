# Research: Módulo 021 - Platform Internationalization

## Decision 1: usar `lang/` com arquivos JSON como catálogo principal

**Decision**: Adotar `lang/en.json`, `lang/es.json` e `lang/pt_BR.json` como fonte primária das traduções do recorte central, mantendo a chave padrão como a string original usada em `__()`.

**Rationale**: A documentação oficial do Laravel 12 recomenda JSON translations para aplicações com grande volume de strings. O projeto já usa `__()` diretamente nas views, então JSON reduz fricção e evita inventário artificial de chaves curtas para o recorte inicial.

**Alternatives considered**:
- `lang/{locale}/*.php`: útil para domínios grandes, mas aumentaria o custo inicial do recorte central.
- Traduções apenas em banco: adicionaria complexidade desnecessária para o primeiro módulo de internacionalização.

## Decision 2: resolver locale por request com middleware central e `App::setLocale()`

**Decision**: Criar middleware dedicado para requests web/admin que leia preferência do operador autenticado, valide contra a publicação ativa e aplique `App::setLocale()` em runtime.

**Rationale**: O Laravel 12 permite alterar o locale em runtime por request usando `App::setLocale()`. Isso encaixa diretamente na necessidade de preferência por operador sem alterar o locale global da aplicação.

**Alternatives considered**:
- Alterar `config('app.locale')` globalmente: não atende preferência por operador.
- Resolver locale apenas em Livewire components: deixaria login, layout e controllers fora do mesmo fluxo.

## Decision 3: manter governança em tabelas centrais separadas dos arquivos de idioma

**Decision**: Persistir apenas preferência do operador, publicação ativa, snapshots de cobertura e lacunas detectadas em tabelas centrais.

**Rationale**: A governança precisa de histórico, rollback e inspeção. Os arquivos `lang/` continuam como fonte estática versionada; o banco central registra o que está ativo e o que foi validado.

**Alternatives considered**:
- Salvar todas as traduções no banco: aumenta muito a superfície operacional.
- Não persistir publicações: inviabiliza rollback e auditoria.

## Decision 4: medir cobertura por lista governada de chaves obrigatórias

**Decision**: Definir em configuração uma lista mínima de strings obrigatórias para autenticação, navegação e dashboard administrativo, e medir a cobertura por locale com base nela.

**Rationale**: O roadmap pede suporte a múltiplos idiomas, mas a implementação incremental precisa de um recorte verificável. Cobertura por chaves obrigatórias evita prometer tradução total do ERP inteiro neste módulo.

**Alternatives considered**:
- Varredura automática de todas as views: pouco previsível e ruidosa para o primeiro módulo.
- Medir apenas presença do arquivo JSON: superficial demais para governança real.

## Decision 5: rollback restaura a última publicação saudável sem sobrescrever preferências

**Decision**: O rollback troca apenas a publicação ativa e o fallback, sem apagar a preferência persistida do operador; na resolução do request, preferências incompatíveis caem automaticamente no fallback ativo.

**Rationale**: Preferência por operador é intenção de usuário; publicação ativa é governança operacional. Misturar os dois em rollback apagaria contexto útil e aumentaria risco operacional.

**Alternatives considered**:
- Reescrever preferências dos operadores no rollback: invasivo e desnecessário.
- Ignorar validação de preferência após rollback: poderia deixar requests resolvendo locale fora da publicação ativa.
