# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 1 foi publicada nas versões `v0.2.0` a `v0.2.2`. A estrutura organizacional saiu em `v0.3.0`, a camada bruta KBF em `v0.3.1`, pessoas/vínculos em `v0.4.0`, a folha mensal em `v0.5.0` e a revisão auditável de identidades em `v0.6.0`, após CI aprovado no commit `20674fd`. A Fase 2 está concluída. O cadastro público está desabilitado e o login é reservado ao painel administrativo.

Evidências atuais: 55 testes PHP com 252 asserções, Pint, ESLint, `vue-tsc`, build Vite, Composer audit e npm audit passaram. Todas as migrations passaram no MySQL 8.4.7 e os cinco agendamentos estão registrados. A geração real criou 42 candidatos para 42 grupos de nomes repetidos. Testes comprovam autenticação obrigatória, decisão auditável, idempotência e ausência de fusão. As execuções KBF anteriores preservam 1.628 vínculos e 1.556 pagamentos de julho/2026. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Próximo passo após fechar a Fase 2: iniciar a Fase 3 com busca pública de pessoas e perfis com proveniência e histórico de remuneração.
