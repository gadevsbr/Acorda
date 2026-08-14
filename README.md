# Acorda Alcobaça

Plataforma independente de transparência pública que torna dados oficiais de Alcobaça (BA) pesquisáveis, compreensíveis e auditáveis. O produto apresenta fatos, contexto, fonte e data de atualização, sem avaliações político-partidárias.

## Estado atual

A Fase 0 estabelece a fundação executável. Ainda não há dados públicos importados e nenhuma tela deve ser interpretada como MVP de dados pronto. Consulte [ROADMAP.md](ROADMAP.md).

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

Os coletores serão independentes e idempotentes em `app/Collectors/`. Nenhum coletor real foi habilitado na Fase 0. Toda futura ingestão deverá armazenar o registro bruto antes da normalização e usar fixtures nos testes.

## Segurança e contribuição

Nunca envie `.env`, tokens, senhas, dumps ou documentos privados. Leia [AGENTS.md](AGENTS.md) e a memória técnica antes de alterar o projeto.
