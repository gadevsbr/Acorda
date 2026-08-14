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

PNCP, TSE, Câmara de Alcobaça e TCM-BA permanecem planejados e não integrados.
