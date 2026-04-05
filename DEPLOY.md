# Deploy - GitHub Actions + Docker

## Visão Geral

Este documento explica como funciona o deploy automático do sistema utilizando GitHub Actions e Docker.

---

## Arquitetura de Deploy

```
GitHub Repository
      │
      ▼
GitHub Actions (Build + Test)
      │
      ▼
Docker Hub / Container Registry
      │
      ▼
Servidor de Produção (Docker + Docker Compose)
```

---

## Fluxo de Deploy

### 1. Push para Main/Master

```bash
git push origin main
```

### 2. GitHub Actions Executa

1. **Testes**: Executa PHPUnit no container do Laravel
2. **Build Frontend**: Executa `npm run build` no React/Vite
3. **Build Backend**: Executa `composer install --no-dev --optimize-autoloader`
4. **Build Docker**: Constrói as imagens para produção

### 3. Imagens Publicadas

As imagens Docker são publicadas no registry:
- `ghcr.io/usuario/comunidade-amigos-leituras/app:latest`
- `ghcr.io/usuario/comunidade-amigos-leituras/web:latest`
- `ghcr.io/usuario/comunidade-amigos-leituras/frontend:latest`

### 4. Servidor de Produção

No servidor, basta executar:

```bash
docker-compose pull
docker-compose up -d
```

---

## GitHub Actions Workflow

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run PHP tests
        run: |
          docker build -t app test -f docker/php/Dockerfile
          docker run app php /var/www/artisan test

  build-and-push:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Build and push Docker images
        uses: docker/build-push-action@v5
        with:
          push: true
          tags: ghcr.io/${{ github.repository }}/app:latest
```

---

## Servidor de Produção

### Requisitos

- Docker >= 20.10
- Docker Compose >= 2.0
- 4GB RAM mínimo
- 20GB disco

### Configuração

1. Criar arquivo `.env` no servidor:

```env
APP_ENV=production
DB_HOST=db
DB_PORT=5432
DB_DATABASE=comunidade_db
DB_USERNAME=user_admin
DB_PASSWORD=senha_segura_aqui
REDIS_HOST=redis
FRONTEND_URL=https://seudominio.com
```

2. Criar arquivo `docker-compose.prod.yml`:

```yaml
services:
  app:
    image: ghcr.io/usuario/comunidade-amigos-leituras/app:latest
    environment:
      - APP_ENV=production
    volumes:
      - ./backend:/var/www
      - ./storage:/var/www/storage

  web:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf

  db:
    image: postgres:15-alpine
    environment:
      - POSTGRES_DB=comunidade_db
      - POSTGRES_USER=user_admin
      - POSTGRES_PASSWORD=senha_segura_aqui
    volumes:
      - db_data:/var/lib/postgresql/data

  redis:
    image: redis:alpine

  frontend:
    image: ghcr.io/usuario/comunidade-amigos-leituras/frontend:latest
    ports:
      - "3000:3000"
```

3. Executar:

```bash
docker-compose -f docker-compose.prod.yml up -d
```

---

## Deploy Manual (Sem CI/CD)

### Backend

```bash
cd backend
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

### Frontend

```bash
cd frontend
npm install
npm run build
```

### Docker Compose

```bash
docker-compose up -d --build
```

---

## Rollback

Para reverter para uma versão anterior:

```bash
# Listar imagens disponíveis
docker images | grep comunidade

# Puxar versão anterior
docker pull ghcr.io/usuario/comunidade-amigos-leituras/app:versao_anterior

# Atualizar docker-compose para usar versão anterior
# Editar docker-compose.yml e definir a versão

docker-compose up -d
```

---

## Monitoramento

### Logs

```bash
# Todos os serviços
docker-compose logs -f

# Apenas backend
docker-compose logs -f app

# Apenas erros
docker-compose logs -f app | grep -i error
```

### Health Checks

- Backend: `GET /api/user` (retorna 401 se não autenticado)
- Frontend: `GET /` (retorna página HTML)
- Database: PostgreSQL disponível na porta 5432

---

## SSL/HTTPS

Para usar HTTPS em produção:

1. Obter certificado (Let's Encrypt ou comprá-lo)
2. Configurar Nginx com certificado:

```nginx
server {
    listen 443 ssl http2;
    server_name seu-dominio.com;
    
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    
    location / {
        # ...
    }
}
```

3. Redirecionar HTTP para HTTPS:

```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    return 301 https://$server_name$request_uri;
}
```

---

## Variáveis de Ambiente de Produção

| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| APP_ENV | Ambiente | `production` |
| APP_DEBUG | Debug mode (false em prod) | `false` |
| DB_* | Configuração do banco | - |
| REDIS_HOST | Host do Redis | `redis` |
| FRONTEND_URL | URL do frontend | `https://...` |
| SANCTUM_STATEFUL_DOMAINS | Domínios permitidos | `seudominio.com` |

---

## Troubleshooting

### Container não inicia

```bash
docker-compose logs app
```

### Banco de dados não conecta

```bash
# Verificar se o banco está rodando
docker-compose ps db

# Testar conexão
docker-compose exec db psql -U user_admin -d comunidade_db
```

### assets não carregam

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Próximos Passos

1. Configurar GitHub Actions no repositório
2. Configurar registry (Docker Hub ou GitHub Container Registry)
3. Configurar servidor de produção
4. Configurar domínio e SSL
5. Configurar monitoramento (opcional: Sentry, Grafana)