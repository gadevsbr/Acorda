# Fontes de dados

## Prefeitura Municipal de Alcobaça — Dados Abertos

Entidade: Prefeitura Municipal de Alcobaça (BA)  
URL: `https://www.acessoinformacao.com.br/transparencia/entidades/ba/alcobaca/dados-abertos`  
- API: `/folha-pagamento` integrada; demais endpoints planejados
- Formato: JSON ou XML por `Accept`; o coletor usa JSON
- Método: GET REST, sem scraping
- Autenticação: nenhuma observada
- Paginação: `page` iniciado em 1; documentação informa `per_page` até 500
- Rate limit: não publicado; usar execução diária, retries moderados e sem varredura agressiva
Campos: folha, autoridades, contratos, fiscais, licitações, despesas, documentos e diários  
- Último teste: 2026-08-14, HTTP 200, JSON válido, `total=0`, `per_page=50`, `last_page=0`
- Status: parcial
Observações: a documentação oficial lista os campos necessários. A resposta vazia atual não confirma ausência de servidores nem indisponibilidade histórica. O servidor não retornou ETag ou Last-Modified no teste controlado.

## Prefeitura Municipal de Alcobaça — Estrutura Organizacional

- API: `/estrutura-organizacional`
- Último teste: 2026-08-14, HTTP 200, 12 registros, uma página solicitada com `per_page=100`
- Campos observados: ID, nome, responsável, contatos, endereço, competências, funcionamento, órgão vinculado e datas da fonte
- Status: operacional e integrado
- Normalização: responsável preservado como texto; hierarquia apenas por nome exato e único

## Prefeitura Municipal de Alcobaça — Transparência RH / KBF

- Entidade: Prefeitura Municipal de Alcobaça (BA)
- Página oficial de encaminhamento: `https://alcobaca.ba.gov.br/link/transparencia-rh.php`
- Servidores ativos: formulário KBF `formID=229`
- Remuneração por servidor: formulário KBF `formID=278`
- API pública documentada: nenhuma localizada
- Formato observado: aplicação Webrun com sessão, execução de fluxo e grade estruturada
- Último teste: 2026-08-14, HTTP 200, 1.628 vínculos ativos, sete campos por linha
- Campos: matrícula, nome, admissão, centro de custo, regime, cargo/função e jornada mensal
- Status: operacional; servidores ativos preservados e normalizados
- Remuneração: competência julho/2026 integrada com 1.556 pagamentos; totais conferidos em centavos contra a grade oficial
- Observações: é o destino atual publicado pelo site oficial da Prefeitura. A fonte esteve temporariamente indisponível e depois voltou a responder. O cliente confere o total declarado e interrompe a coleta se o contrato da grade mudar. Matrícula é identidade do vínculo, não prova de identidade civil. Oito pagamentos não encontraram vínculo ativo correspondente e permanecem sem associação.

## SoftHaas — Folha legada

- URL: `https://alcobaca.transparencia.softhaas.com/`
- Períodos visíveis: 2020 e 2021
- Status: legado; não integrado
- Observações: não tratar como fonte corrente. Pode servir futuramente para histórico após validação de origem, estabilidade e método de acesso.

PNCP, TSE, Câmara de Alcobaça e TCM-BA permanecem planejados e não integrados.
