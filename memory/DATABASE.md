# Banco de dados

## Estado atual

MySQL é o banco de produção. As migrations iniciais do Laravel criam:

- `users` e tabelas de autenticação;
- `cache` e `cache_locks`;
- `jobs`, `job_batches` e `failed_jobs`.

SQLite em memória/arquivo é permitido somente para testes locais e CI.

As conexões MySQL/MariaDB fixam o engine `InnoDB`. Isso garante transações e chaves estrangeiras mesmo em ambientes como o WampServer atual, cujo padrão global estava configurado como MyISAM.

## Núcleo de ingestão

- `sources`: catálogo, entidade, município, URLs, estado e última coleta bem-sucedida;
- `source_health_checks`: condição observada, HTTP, latência, contagem, schema e mensagem;
- `collector_runs`: duração, versão, checkpoints, contadores e erros;
- `collector_checkpoints`: estado idempotente por fonte/coletor/chave;
- `raw_source_records`: payload bruto, origem, datas, metadados HTTP, checksum e validação.
- `source_alerts`: tipo, severidade, ocorrências, resolução e estado da notificação externa.
- `organizations`: órgão atual, slug, tipo, contatos, responsável textual, hierarquia e referência ao registro bruto.
- `people`: identidade conservadora de fonte, vinculada à matrícula e ao registro bruto mais recente;
- `positions`: catálogo municipal deduplicado somente por nome normalizado exato;
- `employments`: vínculo por matrícula, cargo, admissão, regime, centro de custo, jornada e estado corrente observado.

A unicidade de `raw_source_records` é `(source_id, external_id, checksum)`: repetir o mesmo payload é idempotente; mudar conteúdo cria uma revisão auditável. Exclusões de fonte são restritas quando há histórico bruto.
