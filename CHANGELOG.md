# Changelog

O formato segue Keep a Changelog e o projeto usa versionamento semântico.

## [Unreleased]

## [0.11.0] — 2026-08-15

### Added

- Documentos oficiais nos perfis de contratos e licitações.
- Lotes de licitação com item, objeto, quantidade, unidade e valor estimado.
- Links de proveniência apontando para o registro específico da API oficial.

## [0.10.0] — 2026-08-14

### Added

- Tabelas normalizadas de fornecedores, licitações e contratos, com valores monetários em centavos.
- Relações conservadoras por IDs oficiais entre contrato, fornecedor e licitação.
- Consultas e perfis públicos para os três datasets, com busca, valores, fonte e contexto.
- Comando `procurement:normalize` agendado após as coletas.

### Security

- CPF de fornecedor pessoa física é mascarado na interface; CNPJ permanece atribuído à fonte oficial.

## [0.9.0] — 2026-08-14

### Added

- Coletor raw-first comum para fornecedores, contratos, licitações e fiscais de contrato.
- Validação específica, paginação, checkpoint, saúde, comando e agendamento por recurso.
- Carga real de 361 fornecedores, 1.275 contratos e 1.073 licitações sem registros inválidos.

### Changed

- Campos opcionais do OpenAPI permanecem nulos sem invalidar o registro; somente identidade e tipos presentes são exigidos.

## [0.8.0] — 2026-08-14

### Added

- Cliente e coletor paginado para despesas oficiais, com allowlist, validação, checksum, checkpoint e histórico bruto idempotente.
- Comando `collect:prefeitura-expenses` e agendamento diário compatível com hospedagem compartilhada.

### Security

- Resposta oficial vazia recebe estado parcial e não comprova inexistência de despesas ou fornecedores.

## [0.7.0] — 2026-08-14

### Added

- Busca pública por nome ou matrícula, limitada a 50 correspondências e sem diretório irrestrito.
- Perfis funcionais com vínculos, remuneração por competência, versões corrigidas e proveniência oficial por registro.
- Avisos explícitos sobre homônimos, identidade civil e ausência inconclusiva de resultados.

### Changed

- Home passou a oferecer acesso direto à busca de pessoas e aos valores individuais publicados.

## [0.6.1] — 2026-08-14

### Fixed

- Home deixou de exibir o aviso inicial desatualizado e passou a calcular contagens reais do banco.
- Links públicos respeitam a instalação em subdiretório do Wamp, incluindo `/acorda/public`.

### Added

- Resumo público de órgãos, vínculos, cargos, pagamentos, competência, total líquido e saúde das fontes.
- Estado honesto e separado para instalações ainda vazias.

## [0.6.0] — 2026-08-14

### Added

- Geração idempotente de 42 candidatos de identidade por nome normalizado exatamente igual.
- Fila administrativa autenticada para confirmar ou rejeitar candidatos com justificativa, revisor e horário.
- Preservação das identidades de origem mesmo após confirmação revisada.
- Agendamento diário da geração de candidatos.

## [0.5.0] — 2026-08-14

### Added

- Coleta da remuneração KBF por competência, com 1.556 pagamentos reais de julho/2026.
- Valores monetários em centavos inteiros, validação da equação bruto menos descontos igual a líquido e conferência integral da grade.
- Revisões imutáveis encadeadas para correções da fonte e associação conservadora por matrícula.
- Comando `collect:kbf-payroll` e agendamento diário da competência anterior.

## [0.4.0] — 2026-08-14

### Added

- Normalização de 1.628 pessoas/vínculos e 203 cargos/funções, sempre rastreável ao registro bruto.
- Encerramento observado somente após conjunto integral válido; nomes iguais permanecem identidades separadas por matrícula.

## [0.3.1] — 2026-08-14

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
