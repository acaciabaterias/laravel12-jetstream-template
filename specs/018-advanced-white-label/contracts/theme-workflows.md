# Contract: Theme Workflows

## Workflow 1: Cadastro e publicação

1. Operação registra `BrandIdentityProfile`.
2. Operação associa `ThemeAssetRecord` e tokens obrigatórios.
3. Sistema cria `TenantThemeVersion` em `draft`.
4. Operação executa validação mínima.
5. Sistema cria `ThemePublicationRecord`.
6. Se validação passar, a versão vira `published`.
7. Sistema publica `TEMA_WHITE_LABEL_PUBLICADO`.

## Workflow 2: Rollback visual

1. Operação identifica problema em uma versão publicada.
2. Sistema localiza a última versão saudável disponível.
3. Operação registra motivo da reversão.
4. Sistema cria `ThemeRollbackEvidence`.
5. Sistema restaura a versão saudável.
6. Sistema publica `ROLLBACK_TEMA_WHITE_LABEL_EXECUTADO`.
