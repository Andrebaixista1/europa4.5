# Europa 4.5

Base Laravel (clone visual do `luminarisai-v2`) adaptada para o projeto **Europa 4.5**.

## Status atual

- Branding atualizado para `Europa 4.5` (logo + titulos).
- Tema ajustado para acento azul `#007AFF` (claro/escuro).
- Navegacao de `Configuracoes` removida do menu.
- Login em PT-BR.
- Perfil (`/profile`) salvando no SQL Server real (`europa45.dbo.users`).
- Login local compativel com:
  - usuarios reais na tabela `users` (senha `bcrypt`)
  - credenciais demo por `.env` (para testes)

## Stack

- PHP 8.2+
- Laravel 12
- Blade + Tailwind CSS
- Vite
- SQL Server (`sqlsrv` / `pdo_sqlsrv`)

## Requisitos locais (Windows/WAMP)

- PHP 8.2 (WAMP)
- Extensoes PHP:
  - `pdo_sqlsrv`
  - `sqlsrv`
- ODBC Driver 17 ou 18 for SQL Server

## Configuracao local

1. Instalar dependencias:

```bash
composer install
npm install
```

2. Preparar ambiente:

```bash
copy .env.example .env
php artisan key:generate
```

3. Configurar banco SQL Server no `.env` (exemplo):

```env
DB_CONNECTION=sqlsrv
DB_HOST=177.153.62.236
DB_PORT=1433
DB_DATABASE=europa45
DB_USERNAME=SEU_USUARIO
DB_PASSWORD=SUA_SENHA
DB_ENCRYPT=true
DB_TRUST_SERVER_CERTIFICATE=true

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

4. Compilar assets:

```bash
npm run build
```

5. Rodar:

```bash
php artisan optimize:clear
php artisan serve
```

## Login

- O projeto pode autenticar com usuario real (`dbo.users`) usando:
  - `login`
  - `password` (`bcrypt`)
- Tambem aceita logins demo via `.env` (para teste local), por exemplo:

```env
DEMO_LOGIN=admin
DEMO_PASSWORD=admin
```

## Banco `europa45` (RBAC)

Scripts incluidos:

- `database/sql/bootstrap_rbac_europa45.sql`
  - cria tabelas de RBAC (`users`, `roles`, `permissions`, etc.) e seed inicial
- `database/sql/patch_users_compat.sql`
  - adiciona colunas de compatibilidade do Laravel (`remember_token`, `email_verified_at`) em `dbo.users`

## Observacoes

- O model `User` foi adaptado para o schema real (`nome`, `login`, `email`, `password`, `equipe_id`, `role_id`, `ativo`).
- O campo exibido como `name` no Laravel e mapeado para `nome` na tabela SQL Server.
