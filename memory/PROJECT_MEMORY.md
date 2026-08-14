# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 1 foi publicada nas versões `v0.2.0` a `v0.2.2`. A estrutura organizacional saiu em `v0.3.0`, a camada bruta KBF em `v0.3.1`, pessoas/vínculos em `v0.4.0`, a folha mensal em `v0.5.0` e a revisão auditável de identidades em `v0.6.0`. A Fase 2 está concluída. A home saiu em `v0.6.1`, busca e perfis em `v0.7.0`, e a coleta raw-first de despesas em `v0.8.0` após CI aprovada no commit `86ed2bc`. A Fase 4 permanece em andamento porque a fonte respondeu vazia.

Evidências atuais: 61 testes PHP com 349 asserções, Pint, ESLint, `vue-tsc`, build Vite, Composer audit e npm audit passaram. A execução real 11 de despesas recebeu HTTP 200, envelope válido e `total=0`, corretamente registrado como parcial. No Edge, a busca real por ABEL mostrou vínculo, competência 07/2026, valores e links oficiais. O Apache atual só encaminha essas rotas com `index.php` explícito, indicando configuração externa de rewrite pendente no Wamp. Todas as migrations passaram no MySQL 8.4.7.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Normalizar e publicar fornecedores, contratos e licitações já preservados, mascarando CPF de pessoa física. Despesas e fiscais continuam aguardando registros reais da fonte.
