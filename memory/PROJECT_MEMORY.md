# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 1 foi publicada nas versões `v0.2.0` a `v0.2.2`. A estrutura organizacional saiu em `v0.3.0`, a camada bruta KBF em `v0.3.1`, pessoas/vínculos em `v0.4.0`, a folha mensal em `v0.5.0` e a revisão auditável de identidades em `v0.6.0`. A Fase 2 está concluída. A home da Fase 3 está conectada ao banco e aguarda publicação. O cadastro público está desabilitado e o login é reservado ao painel administrativo.

Evidências atuais: 56 testes PHP com 276 asserções, Pint, ESLint, `vue-tsc`, build Vite, Composer audit e npm audit passaram. A home foi validada no Edge em `http://localhost/acorda/public/`: mostrou 12 órgãos, 1.628 vínculos, 203 cargos, 1.556 pagamentos, competência 07/2026 e R$ 4.190.802,17 líquidos; o aviso inicial não apareceu e o link de órgãos preservou o subdiretório. Todas as migrations passaram no MySQL 8.4.7. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Próximo passo da Fase 3: busca pública de pessoas e perfis com proveniência e histórico de remuneração.
