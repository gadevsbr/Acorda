# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 1 foi publicada nas versões `v0.2.0` a `v0.2.2`. A estrutura organizacional saiu em `v0.3.0`, a camada bruta KBF em `v0.3.1`, pessoas/vínculos em `v0.4.0`, a folha mensal em `v0.5.0` e a revisão auditável de identidades em `v0.6.0`. A Fase 2 está concluída. A home conectada ao banco foi publicada em `v0.6.1`; busca e perfis públicos foram validados para `v0.7.0`. O cadastro público está desabilitado e o login é reservado ao painel administrativo.

Evidências atuais: 59 testes PHP com 342 asserções, Pint, ESLint, `vue-tsc`, build Vite, Composer audit e npm audit passaram. No Edge, a busca real por ABEL retornou nome e matrícula, e o perfil mostrou o vínculo, a competência 07/2026, valores e links oficiais. O Apache atual só encaminha essas rotas com `index.php` explícito, indicando configuração externa de rewrite pendente no Wamp; a aplicação e as rotas Laravel respondem corretamente. Todas as migrations passaram no MySQL 8.4.7. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Iniciar a Fase 4 com despesas e fornecedores, preservando o fluxo raw-first e a rastreabilidade oficial.
