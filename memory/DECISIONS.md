# Decisões

## DEC-001 — Monólito Laravel com frontend Inertia

Data: 2026-08-14  
Status: Aceita  
Decisão: usar Laravel 13, Inertia, Vue 3, TypeScript e Tailwind CSS.  
Motivação: uma única implantação, navegação moderna e manutenção simples.  
Alternativas: API separada e SPA; Blade sem Vue.  
Consequências: Vite é necessário no build, mas não no servidor de produção.

## DEC-002 — MySQL substitui PostgreSQL

Data: 2026-08-14  
Status: Aceita; substitui a preferência inicial por PostgreSQL.  
Decisão: usar MySQL como banco relacional.  
Motivação: requisito do proprietário e ampla disponibilidade em hospedagem compartilhada.  
Alternativas: PostgreSQL com `pg_trgm`; SQLite.  
Consequências: busca tolerante será implementada com recursos MySQL e abstração de busca, sem depender de extensões PostgreSQL.

## DEC-003 — Banco para filas, cache e sessões no MVP

Data: 2026-08-14  
Status: Aceita  
Decisão: usar drivers de banco e tratar Redis como otimização futura.  
Motivação: permitir deploy compartilhado sem daemon adicional.  
Alternativas: Redis obrigatório; fila síncrona.  
Consequências: workers persistentes são opcionais e o scheduler poderá executar consumo em lotes.

## DEC-004 — Docker opcional

Data: 2026-08-14  
Status: Aceita; substitui Docker como requisito obrigatório de execução.  
Decisão: manter Compose para desenvolvimento reproduzível, sem torná-lo requisito de produção.  
Motivação: o alvo de produção é hospedagem compartilhada.  
Alternativas: deploy exclusivamente conteinerizado.  
Consequências: os dois caminhos precisam permanecer documentados e testáveis.
