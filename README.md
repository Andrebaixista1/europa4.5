# Europa 4.5 (Ruby + Sinatra)

Copia do front do Europa 4 com design atualizado, mantendo a paleta principal e reutilizando os mesmos endpoints da API Laravel.

## Requisitos

- Ruby 3.x
- Bundler

## Como executar

```bash
bundle install
set API_BASE_URL=http://localhost:8000/api
bundle exec ruby app.rb
```

Acesse: `http://localhost:4567`

## Variaveis

- `API_BASE_URL` (opcional): base da API do Europa 4.
  - Default: `http://localhost:8000/api`
- `PORT` (opcional): porta do Sinatra.
- `BIND` (opcional): host de bind do Sinatra.

## Endpoints consumidos pelo front

- `/health-consult`
- `/dashboard/fila/in100`
- `/dashboard/saldos/{handmais,v8,presenca,in100,prata}`
- `/dashboard/consultas/{handmais,v8,presenca,in100,prata}`
