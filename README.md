# Acorda Alcobaça

Plataforma independente de transparência pública que torna dados oficiais de Alcobaça (BA) pesquisáveis, compreensíveis e auditáveis. O produto apresenta fatos, contexto, fonte e data de atualização, sem avaliações político-partidárias.

## Estado atual

A Fase 3 está completa: além do núcleo auditável de ingestão e dos dados da Fase 2, a aplicação oferece busca pública e perfis funcionais com vínculos, remuneração, revisões e fonte oficial. A base validada possui 12 órgãos, 1.628 vínculos ativos, 203 cargos/funções, 1.556 pagamentos de julho/2026 e 42 candidatos revisáveis de identidade. Consulte [ROADMAP.md](ROADMAP.md).

A home calcula suas contagens diretamente do banco e mostra apenas conjuntos efetivamente disponíveis. Em uma instalação vazia, orienta a executar migrations e coletores em vez de exibir números simulados.

## Stack

- PHP 8.3+ e Laravel 13;
- MySQL 8;
- Inertia.js, Vue 3 e TypeScript;
- Tailwind CSS;
- filas, cache e sessões em banco no MVP;
- Vite para compilar assets;
- PHPUnit, Laravel Pint, ESLint e GitHub Actions.

Redis não é requisito do deploy inicial. Docker é opcional para desenvolvimento; a aplicação foi desenhada para funcionar em hospedagem compartilhada.

## Desenvolvimento local no WampServer

Requisitos: PHP 8.3 com `pdo_mysql`, Composer, Node.js 22+ e MySQL.

```powershell
Copy-Item .env.example .env
php composer.phar install
npm.cmd ci
php artisan key:generate
php artisan migrate
npm.cmd run build
php artisan serve
```

Ajuste as variáveis `DB_*` do `.env` antes da migration. Durante desenvolvimento do frontend, use `npm.cmd run dev`.

## Docker opcional

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

No `.env` do Docker use `DB_HOST=mysql`. A aplicação fica em `http://localhost:8080`.

## Filas e scheduler

O driver padrão é `database`:

```bash
php artisan queue:work --tries=3
php artisan schedule:run
```

Em hospedagem compartilhada, configure um cron por minuto:

```cron
* * * * * cd /caminho/do/projeto && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

## Qualidade

```powershell
php artisan test
vendor\bin\pint.bat --test
npm.cmd run lint
npm.cmd run typecheck
npm.cmd run build
```

A CI repete testes, estilo, lint e build em cada push e pull request.

## Deploy em hospedagem compartilhada

Compile `public/build` localmente ou na CI, instale dependências com `composer install --no-dev --optimize-autoloader`, aponte o domínio para `public/`, configure `.env`, rode migrations e caches. O servidor de produção não precisa de Node.js. Veja [docs/DEPLOY_SHARED_HOSTING.md](docs/DEPLOY_SHARED_HOSTING.md).

## Coletores

Os coletores são independentes e idempotentes em `app/Collectors/`. A folha da Prefeitura pode ser consultada com:

```powershell
php artisan collect:prefeitura-payroll --year=2026 --month=7 --max-pages=20 --per-page=100
```

O comando guarda execução, saúde, checkpoint e payload bruto versionado por checksum antes de qualquer normalização. O scheduler o executa diariamente às 02:15. Se o PHP local não possuir CAs configuradas, informe um bundle confiável em `COLLECTOR_CA_BUNDLE`; nunca desative a validação TLS.

Alertas de indisponibilidade, schema alterado, parcialidade e resposta vazia são deduplicados em `source_alerts`. Configure `SOURCE_ALERT_EMAIL` e o mailer do Laravel para receber a primeira ocorrência e cada reabertura; falha no envio é registrada sem interromper a coleta.

A página pública `/fontes` mostra a última condição observada e sempre liga para a fonte oficial.

A busca pública em `/pessoas` aceita nome ou matrícula. Ela exige ao menos dois caracteres e não lista toda a base sem consulta. O perfil `/pessoa/{slug}` mantém cada matrícula separada, inclusive para nomes iguais, e apresenta versões atuais e substituídas da remuneração com seus respectivos registros oficiais.

A estrutura organizacional usa um coletor separado:

```powershell
php artisan collect:prefeitura-organizations --max-pages=10 --per-page=100
```

Somente registros válidos são normalizados. Vínculos entre órgãos exigem correspondência nominal exata e única; o nome do responsável permanece texto atribuído à fonte, sem criação automática de pessoa.

A grade oficial de servidores ativos do KBF pode ser preservada com:

```powershell
php artisan collect:kbf-active-employees
```

O cliente mantém a sessão exigida pelo Webrun, confere o total declarado contra todas as linhas recebidas e falha fechado se o contrato da grade mudar. Após preservar o bruto, normaliza pessoa, cargo/função e vínculo. Cada matrícula gera uma identidade de fonte separada; nomes iguais nunca são fundidos automaticamente. O scheduler executa a coleta diariamente às 03:15.

A remuneração mensal usa uma fonte lógica separada:

```powershell
php artisan collect:kbf-payroll --month=7 --year=2026
```

Valores monetários são guardados em centavos inteiros. Correções criam revisões encadeadas, sem sobrescrever o valor anterior. A competência padrão é o mês anterior e o scheduler repete a coleta diariamente às 03:45 para detectar correções da fonte.

As despesas oficiais são preservadas por `php artisan collect:prefeitura-expenses --max-pages=20 --per-page=100`. O recurso está integrado em modo raw-first e roda diariamente às 05:00. Enquanto a API oficial responder vazia, nenhuma despesa ou fornecedor é inferido ou publicado.

Fornecedores, contratos, licitações e fiscais usam `php artisan collect:prefeitura-procurement {resource}`. Os quatro recursos são coletados diariamente entre 05:10 e 05:40; respostas vazias permanecem parciais e campos opcionais ausentes não são inventados.

Possíveis identidades repetidas são geradas sem fusão automática:

```powershell
php artisan identity:generate-candidates
```

A revisão fica em `/admin/candidatos-identidade`, exige autenticação e justificativa, e registra o usuário e o horário da decisão. Confirmar uma correspondência não apaga nem combina os registros de origem. O scheduler regenera candidatos idempotentemente às 04:15.

## Segurança e contribuição

Nunca envie `.env`, tokens, senhas, dumps ou documentos privados. Leia [AGENTS.md](AGENTS.md) e a memória técnica antes de alterar o projeto.
