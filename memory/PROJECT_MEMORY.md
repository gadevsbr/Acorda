# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. O núcleo da Fase 1 foi publicado como `v0.2.0`; alertas deduplicados com e-mail opcional foram publicados em `v0.2.1`. Estão implementados fontes, saúde, registros brutos, execuções, checkpoints, cliente/coletor da folha e página `/fontes`. O cadastro público está desabilitado e o login será reservado ao painel administrativo. Ainda não existem pessoas ou folha normalizada.

Evidências atuais: 38 testes PHP com 136 asserções, Pint, ESLint, `vue-tsc`, build Vite, CI GitHub e smoke HTTP 200 em `/` e `/fontes` passaram. As novas migrations passaram no MySQL 8.4.7. A coleta real controlada retornou HTTP 200 e envelope válido, mas `total=0`; foi corretamente persistida como `partial`, sem inferir ausência de servidores. Um alerta `unexpected_empty` foi persistido como aberto e sem e-mail configurado. O WampServer exigiu `COLLECTOR_CA_BUNDLE` local; a verificação TLS permaneceu habilitada. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Bloqueio para a Fase 2: a API estruturada da folha responde vazia. O site oficial atual aponta servidores/remuneração para formulários KBF sem API documentada visível; o SoftHaas encontrado só lista 2020–2021 e é legado. Investigar chamadas estruturadas do KBF ou obter resposta oficial não vazia antes de normalizar. Não criar scraper HTML frágil nem fixture apresentada como oficial.
