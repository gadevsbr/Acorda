# Coletores

| Coletor | Finalidade | Checkpoint | Última execução | Quantidade | Erros | Status |
|---|---|---|---|---:|---|---|
| Prefeitura / Folha | Preservar folha bruta | paginação salva; próxima página 1 | 2026-08-14 | 0 | primeira tentativa falhou por CA local; repetição HTTP 200 | parcial |
| Prefeitura / Estrutura Organizacional | Preservar e normalizar órgãos | paginação concluída; próxima página 1 | 2026-08-14 | 12 | nenhum na execução validada | operacional |

Atualize esta tabela após cada execução relevante. Falha ou resposta vazia nunca deve apagar dados previamente coletados.

Comando: `php artisan collect:prefeitura-payroll`. Scheduler: diário às 02:15. Versão do coletor: `1.0.0`. A execução aceita ano/mês, limite de páginas e tamanho de página; o domínio e redirects são controlados.

Alertas: `unexpected_empty`, `source_partial`, `source_unavailable` e `schema_changed`. São deduplicados por fonte/tipo, contam ocorrências, resolvem quando a saúde volta a operacional e podem enviar e-mail via `SOURCE_ALERT_EMAIL`.

Estrutura organizacional: `php artisan collect:prefeitura-organizations`, diariamente às 02:45, versão `1.0.0`. Registros inválidos permanecem brutos e não são normalizados.
