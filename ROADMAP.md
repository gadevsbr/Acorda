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

- [x] fontes e saúde das fontes
- [x] registros brutos, checkpoints e execuções de coletores
- [x] primeiro cliente da API da Prefeitura com fixture e validação de schema
- [x] alertas de quebra e observabilidade com deduplicação e e-mail opcional

## Fase 2 — Pessoas

- [x] organizações e hierarquia com fonte oficial
- [x] preservação bruta dos vínculos ativos do KBF (1.628 linhas)
- [x] cargos, pessoas e vínculos funcionais com identidade conservadora por matrícula
- [x] folha mensal sem sobrescrita histórica (julho/2026, 1.556 pagamentos)
- [x] candidatos e revisão autenticada de resolução de identidade, sem fusão automática

## Fase 3 — Busca e perfil

- [x] home cívica conectada aos dados reais
- [x] busca pública de pessoas
- [x] perfil com remuneração, histórico e fonte oficial
- [x] pipeline real validado de ponta a ponta (coleta → normalização → busca → perfil)

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
