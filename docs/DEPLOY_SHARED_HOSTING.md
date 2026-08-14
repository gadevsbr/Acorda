# Deploy em hospedagem compartilhada

## Requisitos

- PHP 8.3+ com `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml` e `ctype`;
- MySQL 8 ou versão compatível oferecida pelo provedor;
- domínio apontado para a pasta `public/`;
- cron, necessário para scheduler e coletores.

## Pacote

Na máquina de build ou CI:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan test
```

Envie a aplicação incluindo `vendor/` e `public/build/` se a hospedagem não oferecer Composer ou Node.js. Nunca envie o `.env` local.

## Ativação

1. Crie o banco e usuário MySQL com privilégios limitados ao banco da aplicação.
2. Crie `.env` diretamente no servidor e gere `APP_KEY`.
3. Garanta escrita em `storage/` e `bootstrap/cache/`.
4. Execute `php artisan migrate --force`.
5. Execute `php artisan optimize`.
6. Crie o link de storage se necessário: `php artisan storage:link`.
7. Configure o cron `schedule:run` por minuto.
8. Verifique `/up`, a home, logs e permissões.

Se o provedor obrigar o domínio em `public_html`, mantenha o núcleo Laravel fora dessa pasta e exponha somente o conteúdo de `public/`, ajustando os caminhos do `index.php` de maneira documentada.

## Atualização e retorno

Antes do deploy, faça backup do banco. Ative modo de manutenção apenas quando uma migration exigir. Preserve o pacote anterior e reverta código e banco de forma compatível em caso de falha. Segredos são gerenciados pelo painel da hospedagem, nunca pelo Git.
