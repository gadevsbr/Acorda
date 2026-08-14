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

## Prefeitura Municipal de Alcobaça — Transparência RH / KBF

- Entidade: Prefeitura Municipal de Alcobaça (BA)
- Página oficial de encaminhamento: `https://alcobaca.ba.gov.br/link/transparencia-rh.php`
- Servidores ativos: formulário KBF `formID=229`
- Remuneração por servidor: formulário KBF `formID=278`
- API: nenhuma API estruturada foi localizada na investigação de 2026-08-14
- Formato observado: aplicação web com iframe/formulário
- Status: descoberta; não integrada
- Observações: é o destino atual publicado pelo site oficial da Prefeitura. Antes de qualquer automação, investigar chamadas estruturadas e termos; não criar scraper HTML frágil.

## SoftHaas — Folha legada

- URL: `https://alcobaca.transparencia.softhaas.com/`
- Períodos visíveis: 2020 e 2021
- Status: legado; não integrado
- Observações: não tratar como fonte corrente. Pode servir futuramente para histórico após validação de origem, estabilidade e método de acesso.

PNCP, TSE, Câmara de Alcobaça e TCM-BA permanecem planejados e não integrados.
