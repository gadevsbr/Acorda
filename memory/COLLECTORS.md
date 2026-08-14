# Coletores

| Coletor | Finalidade | Checkpoint | Última execução | Quantidade | Erros | Status |
|---|---|---|---|---:|---|---|
| Prefeitura / Folha | Preservar folha bruta | paginação salva; próxima página 1 | 2026-08-14 | 0 | primeira tentativa falhou por CA local; repetição HTTP 200 | parcial |
| Prefeitura / Estrutura Organizacional | Preservar e normalizar órgãos | paginação concluída; próxima página 1 | 2026-08-14 | 12 | nenhum na execução validada | operacional |
| Prefeitura / KBF Servidores Ativos | Preservar vínculos funcionais brutos | grade integral conferida pelo total | 2026-08-14 | 1.628 | nenhum nas execuções 5 e 6 | operacional |

Atualize esta tabela após cada execução relevante. Falha ou resposta vazia nunca deve apagar dados previamente coletados.

Comando: `php artisan collect:prefeitura-payroll`. Scheduler: diário às 02:15. Versão do coletor: `1.0.0`. A execução aceita ano/mês, limite de páginas e tamanho de página; o domínio e redirects são controlados.

Alertas: `unexpected_empty`, `source_partial`, `source_unavailable` e `schema_changed`. São deduplicados por fonte/tipo, contam ocorrências, resolvem quando a saúde volta a operacional e podem enviar e-mail via `SOURCE_ALERT_EMAIL`.

Estrutura organizacional: `php artisan collect:prefeitura-organizations`, diariamente às 02:45, versão `1.0.0`. Registros inválidos permanecem brutos e não são normalizados.

Servidores ativos KBF: `php artisan collect:kbf-active-employees`, diariamente às 03:15, versão `1.0.0`. A execução 5 criou 1.628 registros válidos e a execução 6 confirmou idempotência com 1.628 inalterados. O coletor exige sessão, confere o total entregue e não normaliza pessoas ou remunerações nesta etapa.
