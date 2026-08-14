# Changelog

O formato segue Keep a Changelog e o projeto usa versionamento semântico.

## [Unreleased]

### Added

- Cliente session-aware da grade oficial de servidores ativos do KBF.
- Coletor raw-first com conferência fail-closed do total, validação por registro, idempotência e agendamento diário.
- Evidência real de 1.628 vínculos ativos preservados sem registros inválidos.

## [0.3.0] — 2026-08-14

### Added

- Coletor independente da estrutura organizacional oficial.
- Normalização de órgãos com proveniência até o registro bruto.
- Hierarquia somente por correspondência nominal exata e única.
- Páginas públicas `/orgaos` e `/orgao/{slug}`.
- Fixture oficial sanitizada e testes de validação, idempotência, hierarquia e páginas.

## [0.2.2] — 2026-08-14

### Documentation

- Registrada a descoberta do portal RH/KBF atual e do SoftHaas legado, sem assumir integração ou completude.

## [0.2.1] — 2026-08-14

### Added

- Alertas deduplicados com resolução automática e e-mail opcional que não bloqueia a coleta.

## [0.2.0] — 2026-08-14

### Added

- Núcleo auditável com fontes, saúde, execuções, checkpoints e registros brutos.
- Cliente da folha da Prefeitura com paginação, retries, timeout, allowlist e TLS verificável.
- Versionamento de payload por checksum e retomada segura de paginação.
- Comando e scheduler diário para a coleta de folha.
- Página pública `/fontes` com condição observada e link oficial.
- Fixtures sanitizadas e testes de paginação, idempotência, revisão, schema e fonte vazia.

### Security

- Redirects recusados pelo cliente e domínio limitado à allowlist oficial.
- Mudança de schema interrompe publicação e gera saúde `schema_changed`.

## [0.1.0] — 2026-08-14

### Added

- Fundação Laravel 13 com Inertia, Vue 3, TypeScript e Tailwind CSS.
- Configuração MySQL, filas em banco, Docker opcional e guia de hospedagem compartilhada.
- CI para testes PHP, Pint, ESLint e build de produção.
- Governança, roadmap e memória técnica inicial.
- Home institucional própria, responsiva e sem dados simulados.
- Cadastro público desabilitado; autenticação reservada à administração futura.
