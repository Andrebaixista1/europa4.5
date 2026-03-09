# Europa 4.5 Frontend (React)

Frontend separado do backend Laravel.

## Pastas

- Backend Laravel: `D:\Projetos\europa4_l`
- Frontend React: `D:\Projetos\europa45`

## Requisitos

- Node 18+
- Backend Laravel rodando em `http://127.0.0.1:8000`

## Configuracao

1. Copie `.env.example` para `.env` no frontend.
2. Ajuste se necessario:

```env
VITE_BACKEND_URL=http://127.0.0.1:8000
```

## Rodando em desenvolvimento

### 1) Backend

No projeto Laravel:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

### 2) Frontend

No projeto React:

```powershell
npm install
npm run dev
```

Abra:

- Frontend React: `http://127.0.0.1:5173`
- Backend Laravel: `http://127.0.0.1:8000`

## Observacoes

- Autenticacao React usa endpoints `api/front/*` no Laravel.
- Paginas complexas (Configuracoes, Consulta Cliente, Perfil) estao carregando em modo embed do Laravel (`?embedded=1`) para manter o mesmo design e fluxo durante a migracao total para componentes React.
