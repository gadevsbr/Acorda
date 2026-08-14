# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 implementada, validada e publicada como `v0.1.0` em 2026-08-14. O cadastro público está desabilitado e o login será reservado ao painel administrativo. Nenhum coletor ou dado público real foi implementado ainda.

Evidências locais: 24 testes PHP com 60 asserções, Pint, ESLint, `vue-tsc`, build Vite, migrations limpas em SQLite e smoke HTTP 200 passaram. As migrations também passaram no MySQL 8.4.7 local e todas as tabelas foram confirmadas como InnoDB. A primeira execução da CI no GitHub passou em 2026-08-14; actions foram atualizadas para a geração baseada em Node 24 após aviso de depreciação. Docker não está instalado na máquina atual, portanto o Compose foi criado, mas sua execução ainda não foi comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Fase 1: modelar fontes, saúde, registros brutos, execuções e checkpoints; depois integrar o primeiro endpoint oficial da Prefeitura usando fixtures de teste.
