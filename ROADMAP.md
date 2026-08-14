# Roadmap

Legenda: `[ ]` pendente, `[~]` em andamento, `[x]` concluído com evidência.

## Fase 0 — Fundação

- [x] Laravel 13 e PHP 8.3
- [x] Inertia, Vue 3, TypeScript e Tailwind CSS
- [x] configuração MySQL e filas em banco (migrations validadas no MySQL 8.4.7)
- [~] Docker opcional para app, Nginx, MySQL, fila e scheduler (arquivos criados; execução depende de Docker externo)
- [x] documentação e memória do projeto
- [x] CI com testes, estilo, lint e build
- [x] primeira release no GitHub (`v0.1.0`)

## Fase 1 — Núcleo de dados

- [ ] fontes e saúde das fontes
- [ ] registros brutos, checkpoints e execuções de coletores
- [ ] primeiro cliente da API da Prefeitura com fixture e validação de schema
- [ ] alertas de quebra e observabilidade

## Fase 2 — Pessoas

- [ ] organizações, cargos, pessoas e vínculos
- [ ] folha mensal sem sobrescrita histórica
- [ ] candidatos e revisão de resolução de identidade

## Fase 3 — Busca e perfil

- [ ] home cívica e busca de pessoas
- [ ] perfil com remuneração, histórico e fonte oficial
- [ ] pipeline real validado de ponta a ponta

## Fase 4 — Dinheiro público

- [ ] despesas e fornecedores
- [ ] contratos, licitações e fiscais

## Fases seguintes

- [ ] Fase 5: documentos, Diário Oficial e timeline
- [ ] Fase 6: autoridades e histórico eleitoral TSE
- [ ] Fase 7: Câmara e Legislativo
- [ ] Fase 8: validação PNCP e TCM-BA
- [ ] Fase 9: explicador com respostas citadas
- [ ] Fase 10: arquitetura multi-município, sem ativar outro município no MVP
