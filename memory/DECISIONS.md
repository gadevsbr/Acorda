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

## DEC-005 — Revisões brutas por checksum

- Data: 2026-08-14
- Status: Aceita
- Decisão: identificar cada versão bruta por fonte, ID externo e SHA-256 de JSON canônico.
- Motivação: reprocessamento idempotente sem apagar alterações históricas da fonte.
- Alternativas: sobrescrever pelo ID externo; guardar somente o último payload.
Consequências: registros repetidos não duplicam e alterações criam nova versão; o volume cresce de forma auditável.

## DEC-006 — Fonte vazia é estado parcial

- Data: 2026-08-14
- Status: Aceita
- Decisão: resposta válida com zero registros recebe saúde `partial`.
- Motivação: ausência na resposta não comprova ausência do fato nem completude da fonte.
- Alternativas: considerar operacional; considerar indisponível.
Consequências: a interface informa a limitação e não publica conclusões negativas.

## DEC-007 — Uma fonte lógica por dataset

- Data: 2026-08-14
- Status: Aceita
- Decisão: representar folha e estrutura organizacional como fontes lógicas separadas, mesmo compartilhando portal e domínio.
- Motivação: saúde vazia da folha não deve contaminar nem ser escondida pela saúde operacional de organizações.
- Alternativas: uma fonte única para todo o portal.
Consequências: `/fontes` mostra completude por dataset e cada coletor mantém saúde e alertas independentes.

## DEC-008 — Hierarquia organizacional exata

- Data: 2026-08-14
- Status: Aceita
- Decisão: ligar pai e filho somente quando `orgao_vinculado_nome` corresponder de forma normalizada a exatamente um órgão.
- Motivação: evitar hierarquia inventada por similaridade textual.
- Alternativas: fuzzy matching; revisão manual obrigatória para todo vínculo.
Consequências: vínculos ambíguos permanecem sem pai até revisão; nomes de responsáveis não criam pessoas automaticamente.

## DEC-009 — Grade KBF com contrato fail-closed

- Data: 2026-08-14
- Status: Aceita
- Decisão: coletar a grade Webrun oficial com sessão, preservando cada linha bruta e exigindo igualdade entre o total declarado e a quantidade analisada.
- Motivação: o endpoint REST da folha está vazio, enquanto o destino oficial de RH contém os vínculos ativos atuais.
- Alternativas: aguardar indefinidamente a API REST; automatizar o DOM visual; tratar a grade como API estável sem validação.
Consequências: IDs de formulário, fluxo e componentes ficam explícitos e testados; qualquer mudança interrompe a coleta e gera alerta, sem publicar conjunto parcial. A matrícula identifica o vínculo na fonte, mas não autoriza fusão de pessoas nem comprova remuneração.

## DEC-010 — Identidade funcional conservadora por matrícula

- Data: 2026-08-14
- Status: Aceita
- Decisão: criar uma pessoa de fonte para cada matrícula KBF e deduplicar cargos apenas por nome normalizado exato.
- Motivação: 42 grupos possuem nomes repetidos e a fonte não fornece identificador civil seguro para unir matrículas.
- Alternativas: unir pessoas pelo nome; criar uma pessoa por linha/revisão; bloquear toda normalização.
Consequências: homônimos e múltiplas matrículas não são fundidos indevidamente. Candidatos de identidade poderão sugerir revisões futuras, mas exigirão evidência adicional. Centro de custo só liga a órgão por correspondência exata e única; a execução atual produziu zero ligações.

## DEC-011 — Folha mensal imutável e dinheiro em centavos

- Data: 2026-08-14
- Status: Aceita
- Decisão: guardar valores monetários como inteiros em centavos e criar uma nova revisão normalizada quando a fonte corrigir um pagamento.
- Motivação: evitar erro de ponto flutuante e preservar o histórico auditável sem sobrescrita.
- Alternativas: decimal no banco com atualização destrutiva; guardar apenas o payload bruto; considerar a primeira coleta definitiva.
Consequências: cada revisão aponta para o registro bruto e para a revisão substituída; somente `is_latest` muda. A equação bruto menos descontos igual a líquido e a competência solicitada são validadas antes da normalização.

## DEC-012 — Revisão de identidade sem fusão destrutiva

- Data: 2026-08-14
- Status: Aceita
- Decisão: gerar candidatos apenas para nomes normalizados exatamente iguais e registrar confirmação/rejeição administrativa sem fundir pessoas.
- Motivação: nome igual é evidência fraca; a decisão precisa ser auditável e reversível antes de qualquer identidade canônica futura.
- Alternativas: fusão automática; fuzzy matching; ignorar duplicidades.
Consequências: cada decisão exige justificativa e registra revisor e horário. Os registros funcionais e suas proveniências permanecem separados mesmo quando um candidato é confirmado.

## DEC-013 — Perfil público representa identidade funcional

- Data: 2026-08-14
- Status: Aceita
- Decisão: publicar um perfil por pessoa de fonte/matrícula, sem consolidar candidatos confirmados nem apresentar o registro como identidade civil.
- Motivação: a fonte identifica vínculos por matrícula e nomes iguais não bastam para afirmar que se trata da mesma pessoa natural.
- Alternativas: unir perfis por nome; aplicar decisões administrativas diretamente ao público; omitir remuneração individual.
Consequências: homônimos aparecem separadamente na busca. Cada vínculo e pagamento aponta para sua própria proveniência; revisões substituídas continuam visíveis e ausências são descritas como inconclusivas.

## DEC-014 — Despesas vazias permanecem apenas como evidência de coleta

- Data: 2026-08-14
- Status: Aceita
- Decisão: integrar o contrato e a preservação bruta de despesas, mas adiar fornecedores e dados normalizados até a fonte entregar registros reais válidos.
- Motivação: o OpenAPI documenta o schema, porém a resposta atual contém zero registros e não permite validar conteúdo municipal concreto.
- Alternativas: fabricar dados públicos; declarar ausência de despesas; bloquear toda a integração.
Consequências: coletor, checkpoint, saúde e alertas ficam operacionais; a Fase 4 permanece em andamento e a interface não conclui sem evidência.

## DEC-015 — Compras públicas preservadas antes da exposição

- Data: 2026-08-14
- Status: Aceita
- Decisão: preservar fornecedores, contratos e licitações integralmente no bruto antes de criar entidades e relações públicas; aceitar nulos previstos no OpenAPI.
- Motivação: relações entre contratada, fornecedor e licitação precisam usar IDs oficiais, e CPF de pessoa física exige tratamento de privacidade.
- Alternativas: publicar diretamente o JSON; rejeitar campos opcionais nulos; relacionar apenas por nome.
Consequências: 2.709 registros reais estão auditáveis e prontos para normalização conservadora; nenhuma identidade ou relação é inferida por similaridade textual.

## DEC-016 — Relações de compras somente por IDs oficiais

- Data: 2026-08-14
- Status: Aceita
- Decisão: relacionar contratos a fornecedores e licitações exclusivamente pelos IDs publicados; mascarar CPF na apresentação pública.
- Motivação: nomes podem colidir e identificadores de pessoa física exigem minimização na interface.
- Alternativas: relacionar por nome; exibir CPF integral; omitir fornecedores pessoa física.
Consequências: relações ausentes permanecem nulas; CNPJ é exibido como publicado e CPF aparece mascarado.

## DEC-017 — Documentos e lotes derivados do bruto preservado

- Data: 2026-08-15
- Status: Aceita
- Decisão: apresentar anexos, instrumentos e lotes diretamente do payload bruto validado, aceitando somente URLs HTTPS para documentos.
- Motivação: esses elementos são estruturas aninhadas da fonte e não precisam de identidade relacional própria no MVP.
- Alternativas: duplicar tudo em tabelas; ocultar documentos; aceitar qualquer esquema de URL.
Consequências: a proveniência permanece direta e alterações da fonte continuam versionadas por checksum.
