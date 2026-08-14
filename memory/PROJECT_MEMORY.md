# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. Na Fase 1 foram implementados fontes, saúde, registros brutos, execuções, checkpoints, cliente/coletor da folha e a página `/fontes`. O cadastro público está desabilitado e o login será reservado ao painel administrativo. Ainda não existem pessoas ou folha normalizada.

Evidências atuais: 37 testes PHP com 129 asserções, Pint, ESLint, `vue-tsc`, build Vite e smoke HTTP 200 em `/` e `/fontes` passaram. As novas migrations passaram no MySQL 8.4.7. A coleta real controlada retornou HTTP 200 e envelope válido, mas `total=0`; foi corretamente persistida como `partial`, sem inferir ausência de servidores. O WampServer exigiu `COLLECTOR_CA_BUNDLE` local; a verificação TLS permaneceu habilitada. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Concluir a observabilidade da Fase 1 com notificação externa configurável. Em paralelo, investigar competências históricas disponíveis sem varredura agressiva. Só iniciar a normalização da Fase 2 quando houver payload real ou fixture sanitizada derivada de resposta oficial não vazia.
