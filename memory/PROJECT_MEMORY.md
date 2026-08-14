# Memória do projeto

## Objetivo

Construir uma plataforma independente, neutra e auditável de transparência pública para Alcobaça (BA), começando por dados reais da Prefeitura.

## Arquitetura e stack

Monólito modular Laravel 13 em PHP 8.3, MySQL, Inertia/Vue 3/TypeScript e Tailwind. Filas, cache e sessões usam banco inicialmente. Assets são compilados por Vite. Docker é opcional; hospedagem compartilhada é alvo suportado.

O fluxo futuro obrigatório é fonte → registro bruto → validação → normalização → resolução de identidade → banco relacional → busca → frontend. Nenhum fato público será persistido apenas em formato normalizado.

## Estado atual

Fase 0 publicada como `v0.1.0`. A Fase 1 foi publicada nas versões `v0.2.0` a `v0.2.2`. A estrutura organizacional saiu em `v0.3.0`, a camada bruta KBF em `v0.3.1` e pessoas/vínculos em `v0.4.0`. A folha mensal de julho/2026 está integrada localmente e aguarda validação completa e publicação. O cadastro público está desabilitado e o login será reservado ao painel administrativo.

Evidências atuais: 52 testes PHP com 238 asserções, Pint, ESLint, `vue-tsc`, build Vite, Composer audit e npm audit passaram. Todas as migrations, incluindo `payroll_records`, passaram no MySQL 8.4.7 e os quatro agendamentos estão registrados. As execuções KBF 7 e 8 normalizaram 1.628 pessoas/vínculos. As execuções 9 e 10 coletaram de forma idempotente 1.556 pagamentos de julho/2026, zero inválidos, 1.548 ligados a vínculos e totais oficiais exatos em centavos. Docker ainda não está instalado e o Compose continua sem execução comprovada.

## Restrições

- Não fabricar dados nem inferir ausência de fato por resposta vazia.
- Toda informação pública precisa de origem, URL, coleta, checksum e validação.
- Não unir identidades somente por similaridade de nome.
- Não exigir Node.js, Docker, Redis ou worker persistente no servidor compartilhado.

## Próximo passo

Próximo passo da Fase 2: gerar candidatos revisáveis para os 42 grupos de nomes repetidos, sem fusão automática. Depois, a Fase 3 pode publicar busca e perfis com proveniência e histórico de remuneração.
