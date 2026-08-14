# Coletores

| Coletor | Finalidade | Checkpoint | Última execução | Quantidade | Erros | Status |
|---|---|---|---|---:|---|---|
| Prefeitura / Folha | Preservar folha bruta | paginação salva; próxima página 1 | 2026-08-14 | 0 | primeira tentativa falhou por CA local; repetição HTTP 200 | parcial |
| Prefeitura / Estrutura Organizacional | Preservar e normalizar órgãos | paginação concluída; próxima página 1 | 2026-08-14 | 12 | nenhum na execução validada | operacional |
| Prefeitura / KBF Servidores Ativos | Preservar vínculos funcionais brutos | grade integral conferida pelo total | 2026-08-14 | 1.628 | nenhum nas execuções 5 e 6 | operacional |
| Prefeitura / KBF Remuneração | Preservar e normalizar folha mensal | competência 2026-07 integral | 2026-08-14 | 1.556 | nenhum nas execuções 9 e 10 | operacional |

Atualize esta tabela após cada execução relevante. Falha ou resposta vazia nunca deve apagar dados previamente coletados.

Comando: `php artisan collect:prefeitura-payroll`. Scheduler: diário às 02:15. Versão do coletor: `1.0.0`. A execução aceita ano/mês, limite de páginas e tamanho de página; o domínio e redirects são controlados.

Alertas: `unexpected_empty`, `source_partial`, `source_unavailable` e `schema_changed`. São deduplicados por fonte/tipo, contam ocorrências, resolvem quando a saúde volta a operacional e podem enviar e-mail via `SOURCE_ALERT_EMAIL`.

Estrutura organizacional: `php artisan collect:prefeitura-organizations`, diariamente às 02:45, versão `1.0.0`. Registros inválidos permanecem brutos e não são normalizados.

Servidores ativos KBF: `php artisan collect:kbf-active-employees`, diariamente às 03:15, versão `1.1.0`. As execuções 7 e 8 normalizaram de forma idempotente 1.628 pessoas/vínculos e 203 cargos/funções, com zero inválidos. Há 42 grupos de nomes repetidos preservados como identidades distintas por matrícula. Nenhum dos centros de custo correspondeu exatamente aos 12 nomes organizacionais; por segurança, zero associações de órgão foram inferidas.

Remuneração KBF: `php artisan collect:kbf-payroll --month=7 --year=2026`, diariamente às 03:45 usando o mês anterior por padrão, versão `1.0.0`. A execução 9 criou e normalizou 1.556 pagamentos; a execução 10 confirmou idempotência. Totais: R$ 5.606.139,77 brutos, R$ 1.415.337,60 em descontos e R$ 4.190.802,17 líquidos. Foram ligados 1.548 pagamentos a vínculos por matrícula; oito permaneceram sem associação.

Resolução de identidade: `php artisan identity:generate-candidates`, diariamente às 04:15. A execução real encontrou 42 grupos e criou 42 candidatos. O processo só usa nome normalizado exatamente igual e nunca confirma nem funde identidades automaticamente.
