# Instruções para agentes

Antes de alterar o projeto, leia integralmente e nesta ordem:

1. `AGENTS.md`;
2. `README.md`;
3. todos os arquivos de `project-memory/`;
4. `memory/PROJECT_MEMORY.md`;
5. `ROADMAP.md`;
6. os demais arquivos de `memory/` relevantes à tarefa.

## Regras

- Entregue software funcional; não marque trabalho como concluído sem teste ou evidência.
- Preserve a rastreabilidade de todo dado público até sua fonte oficial.
- Nunca fabrique dados públicos e nunca trate ausência na fonte como inexistência do fato.
- Prefira falso negativo a associação indevida de pessoas.
- Use PHP 8.3+, Laravel 13, MySQL, Inertia, Vue 3, TypeScript e Tailwind CSS.
- Mantenha compatibilidade com hospedagem compartilhada: assets compilados, filas em banco e scheduler via cron.
- Não grave segredos. Mantenha `.env`, chaves, dumps, artefatos privados e credenciais fora do Git.
- Consulte `memory/DECISIONS.md` antes de alterar arquitetura. Registre substituições como `Superseded`.
- Atualize `ROADMAP.md`, `CHANGELOG.md` e a memória ao concluir uma etapa relevante.
- Execute, conforme o escopo: testes, Pint, `npm run lint`, `npm run typecheck`, build e migrations.
- Use Conventional Commits. Uma versão validada deve receber tag e release no GitHub.

`memory/` é a memória técnica canônica. `project-memory/` existe como índice de compatibilidade para a convenção operacional do repositório.
