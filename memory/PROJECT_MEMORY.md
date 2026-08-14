# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 2 terminou em `v0.6.0`, seguida da home em `v0.6.1` e busca/perfis em `v0.7.0`. A Fase 4 começou com despesas raw-first em `v0.8.0`; fornecedores, contratos e licitações raw-first foram publicados em `v0.9.0` após CI aprovada no commit `ce47bb7`. A Fase 4 permanece em andamento até a normalização pública.

Evidências atuais: 63 testes PHP com 355 asserções e todos os gates de qualidade passaram. As execuções 12, 16 e 17 preservaram 361 fornecedores, 1.275 contratos e 1.073 licitações sem inválidos; despesas e fiscais responderam vazios e ficaram parciais. O Apache local ainda exige `index.php` explícito nas rotas amigáveis.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Continuar a Fase 4 quando despesas e fiscais entregarem registros reais. Para compras já publicadas, ampliar filtros e documentos sem inferir relações ausentes.
