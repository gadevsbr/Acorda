# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 1 foi publicada nas versões `v0.2.0` a `v0.2.2`. A primeira entrega da Fase 2 foi publicada como `v0.3.0`, após aprovação do CI no commit `6f29ae9`: coletor, normalização, hierarquia e páginas `/orgaos` e `/orgao/{slug}` estão implementados. O cadastro público está desabilitado e o login será reservado ao painel administrativo. Ainda não existem pessoas, cargos ou folha normalizada.

Evidências atuais: 47 testes PHP com 208 asserções, Pint, ESLint, `vue-tsc` e build Vite passaram. Composer e npm audit não encontraram vulnerabilidades conhecidas. As migrations passaram no MySQL 8.4.7 e o scheduler lista as três coletas. A coleta organizacional real trouxe 12 registros em uma página. A API da folha continua vazia, mas o KBF voltou a responder: a execução 5 preservou 1.628 vínculos ativos válidos e a execução 6 confirmou idempotência, sem duplicação. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Próximo passo da Fase 2: normalizar cargos, pessoas e vínculos a partir dos 1.628 registros brutos do KBF, criando uma identidade conservadora por matrícula e candidatos separados para possíveis duplicidades. A remuneração deve ser investigada no formulário 278 sem inferir valores da grade de servidores ativos.
