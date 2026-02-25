# Lumi.A (luminarisai-v2)

Aplicação web em Laravel com interface Blade + Tailwind, autenticação customizada e integração com banco externo `lumia`.

## Stack

- PHP 8.2+
- Laravel 12
- Blade + Tailwind CSS
- Vite
- SQL Server (conexão externa `lumia_sqlsrv`)

## Funcionalidades atuais

- Login customizado com validação de senha em `SHA-256` na tabela externa `lumia_auth_users`.
- Alteração de senha funcional, persistindo `password_sha256` no mesmo banco externo.
- Layout com tema claro/escuro.
- Navegação principal com menu:
  - Painel
  - Configurações
    - Usuários
    - Permissões
- Área de perfil traduzida para PT-BR.

## Configuração local

1. Instale dependências PHP:

```bash
composer install
```

2. Instale dependências front-end:

```bash
npm install
```

3. Copie o arquivo de ambiente e gere a chave:

```bash
cp .env.example .env
php artisan key:generate
```

4. Ajuste as variáveis de conexão externa no `.env`:

```env
LUMIA_DB_CONNECTION=sqlsrv
LUMIA_DB_HOST=SEU_HOST
LUMIA_DB_PORT=1433
LUMIA_DB_DATABASE=lumia
LUMIA_DB_USERNAME=SEU_USUARIO
LUMIA_DB_PASSWORD=SUA_SENHA
```

5. Rode a aplicação:

```bash
php artisan serve
npm run dev
```

## Testes

```bash
php artisan test
```

## Observações

- A autenticação não usa a senha local do usuário Laravel para validar login.
- A validação é feita contra `lumia_auth_users.password_sha256`.

# europa4.5
