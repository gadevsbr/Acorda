# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 1 foi publicada nas versões `v0.2.0` a `v0.2.2`. A estrutura organizacional da Fase 2 saiu em `v0.3.0`; a preservação bruta dos servidores ativos KBF foi publicada em `v0.3.1`. Pessoas, cargos e vínculos agora estão normalizados localmente e aguardam validação completa e publicação. O cadastro público está desabilitado e o login será reservado ao painel administrativo. Remunerações ainda não foram integradas.

Evidências atuais: 48 testes PHP com 220 asserções, Pint, ESLint, `vue-tsc` e build Vite passaram. Composer e npm audit não encontraram vulnerabilidades conhecidas. Todas as migrations passaram no MySQL 8.4.7. As execuções KBF 7 e 8 normalizaram de forma idempotente 1.628 pessoas/vínculos e 203 cargos/funções, com zero inválidos. Existem 42 grupos de nomes repetidos preservados separadamente e zero ligações inferidas entre centro de custo e órgão. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Próximo passo da Fase 2: gerar candidatos revisáveis para os 42 grupos de nomes repetidos, sem fusão automática, e integrar a folha mensal pelo formulário 278 sem sobrescrever histórico.
