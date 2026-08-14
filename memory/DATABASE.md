# Banco de dados

## Estado atual

MySQL é o banco de produção. As migrations iniciais do Laravel criam:

- `users` e tabelas de autenticação;
- `cache` e `cache_locks`;
- `jobs`, `job_batches` e `failed_jobs`.

SQLite em memória/arquivo é permitido somente para testes locais e CI. Ainda não existem tabelas cívicas.

As conexões MySQL/MariaDB fixam o engine `InnoDB`. Isso garante transações e chaves estrangeiras mesmo em ambientes como o WampServer atual, cujo padrão global estava configurado como MyISAM.

## Próxima modelagem

A Fase 1 criará `sources`, `source_health_checks`, `raw_source_records`, `collector_runs` e `collector_checkpoints`. Payload bruto e checksum devem ser preservados antes de qualquer normalização. Índices e relacionamentos serão registrados aqui junto das migrations.
