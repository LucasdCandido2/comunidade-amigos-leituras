# Deploy - Railway

Este guia explica como fazer o deploy da Comunidade Amigos Leituras no Railway.

---

## Onde encontrar o nome do domínio

No Railway, o domínio é gerado automaticamente após você criar o projeto e adicionar serviços:

1. **Criando o projeto no Railway**:
   - Acesse https://railway.com/dashboard
   - Clique em **+ New Project**
   - Escolha **Empty Project** ou conecte ao GitHub
   - Nomeie como "Comunidade Amigos Leituras"

2. **Adicionando serviços**:
   - Clique em **+ New** → **GitHub Repo** (para backend e frontend)
   - Selecione seu repositório GitHub
   - Configure o root directory (backend/ ou frontend/)

3. **Gerando o domínio**:
   - Clique no serviço criado
   - Vá para **Settings** → **Networking**
   - Clique em **Generate Domain**
   - O domínio será algo como: `comunidade-amigos-leituras.up.railway.app`

4. **Domínios por serviço**:
   - Backend API: `backend-seu-projeto.up.railway.app`
   - Frontend: `frontend-seu-projeto.up.railway.app`

---

## Passo a Passo do Deploy

### 1. Preparar o Repositório

O repositório já está configurado com:
- `backend/Dockerfile` - Para o Laravel/PHP
- `frontend/Dockerfile` - Para o React/Vite
- `backend/railway.json` - Configurações do Railway
- `frontend/railway.json` - Configurações do Railway

### 2. Criar Serviços no Railway

#### Backend (Laravel)
1. No Railway, clique em **+ New** → **GitHub Repo**
2. Selecione `comunidade-amigos-leituras`
3. Configure:
   - **Root Directory**: `backend`
   - **Build Command**: `composer install --no-dev --optimize-autoloader`
   - **Start Command**: `php artisan serve --host=0.0.0.0 --port=8000`

#### Frontend (React)
1. Clique em **+ New** → **GitHub Repo**
2. Selecione `comunidade-amigos-leituras`
3. Configure:
   - **Root Directory**: `frontend`
   - **Build Command**: `npm install && npm run build`
   - **Start Command**: `npx serve dist -l 3000`

#### Banco de Dados (PostgreSQL)
1. Clique em **+ New** → **Database** → **PostgreSQL**
2. Nome: `comunidade-db`
3. O Railway cria automaticamente as variáveis:
   - `POSTGRES_DB`
   - `POSTGRES_USER`
   - `POSTGRES_PASSWORD`
   - `POSTGRES_HOST`

#### Redis (Cache)
1. Clique em **+ New** → **Database** → **Redis**
2. Nome: `comunidade-redis`

### 3. Configurar Variáveis de Ambiente

No serviço do **Backend**, adicione em **Variables**:

```env
# App
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:aAkJe3b5zgaab6Un2mbyMFSMVbJofd4zVnLteRDQLoU=

# Database (use referências do Railway)
DB_CONNECTION=pgsql
DB_HOST=${POSTGRES_HOST}
DB_PORT=5432
DB_DATABASE=${POSTGRES_DB}
DB_USERNAME=${POSTGRES_USER}
DB_PASSWORD=${POSTGRES_PASSWORD}

# Redis
CACHE_DRIVER=redis
REDIS_HOST=${REDIS_HOST}
REDIS_PORT=6379

# Frontend URL (substitua pelo seu domínio do frontend)
FRONTEND_URL=https://comunidade-amigos-leituras.up.railway.app
SANCTUM_STATEFUL_DOMAINS=comunidade-amigos-leituras.up.railway.app
```

No serviço do **Frontend**, adicione:

```env
VITE_API_URL=https://backend-seu-projeto.up.railway.app
```

### 4. Configurar Networking

1. No serviço Backend:
   - Settings → Networking → Generate Domain
   - Anote o domínio (ex: `backend-xxx.up.railway.app`)

2. No serviço Frontend:
   - Settings → Networking → Generate Domain
   - Anote o domínio (ex: `frontend-xxx.up.railway.app`)

3. Atualize as variáveis com os domínios reais

### 5. Deploy Automático

O Railway faz deploy automático quando você faz push para GitHub:

1. No Railway, vá em cada serviço
2. Settings → Deploy
3. Configure a branch: `main`
4. Ative **Auto Deploy**

---

## Preciso instalar o CLI do Railway?

**Não é obrigatório**, mas pode facilitar algumas tarefas:

### Instalar CLI (opcional)
```bash
npm install -g @railway/cli
```

### Comandos úteis do CLI
```bash
# Login
railway login

# Ver projetos
railway list

# Ver logs
railway logs -s backend

# Abrir dashboard
railway open

# Variáveis de ambiente
railway variables
```

### Sem CLI
Você pode fazer tudo pelo **Railway Dashboard** (railway.com/dashboard), que é a interface web.

---

## Variáveis de Ambiente Completas

### Backend
| Variável | Valor |
|-----------|-------|
| APP_ENV | production |
| APP_DEBUG | false |
| APP_KEY | (gere com `php artisan key:generate`) |
| DB_CONNECTION | pgsql |
| DB_HOST | ${POSTGRES_HOST} |
| DB_PORT | 5432 |
| DB_DATABASE | ${POSTGRES_DB} |
| DB_USERNAME | ${POSTGRES_USER} |
| DB_PASSWORD | ${POSTGRES_PASSWORD} |
| CACHE_DRIVER | redis |
| REDIS_HOST | ${REDIS_HOST} |
| REDIS_PORT | 6379 |
| FRONTEND_URL | https://seu-dominio.up.railway.app |
| SANCTUM_STATEFUL_DOMAINS | seu-dominio.up.railway.app |

### Frontend
| Variável | Valor |
|-----------|-------|
| VITE_API_URL | https://backend-seu-dominio.up.railway.app |

---

## Solução de Problemas

### Build falha
```bash
# Ver logs no CLI
railway logs -s backend
```

### Banco não conecta
- Verificar se as variáveis de referência estão corretas
- Confirmar que o banco está no mesmo projeto

### assets não carregam
```bash
# No serviço backend, execute:
php artisan config:clear
php artisan cache:clear
php artisan storage:link
```

---

## Custos Railway (Estimativa)

| Serviço | Recursos | Preço |
|---------|----------|-------|
| Backend | 1GB RAM, 1CPU | ~$5/mês |
| Frontend | 512MB RAM | ~$3/mês |
| PostgreSQL | 1GB storage | ~$5/mês |
| Redis | 256MB | ~$3/mês |
| **Total** | | **~$16/mês** |

---

## Próximos Passos Após Deploy

1. **Gerar APP_KEY**:
   ```bash
   php artisan key:generate
   ```

2. **Rodar migrations**:
   ```bash
   php artisan migrate --force
   ```

3. **Criar usuário admin**:
   ```bash
   php artisan db:seed --force --seeder=RolePermissionSeeder
   php artisan db:seed --force --seeder=TestUserSeeder
   ```

4. **Configurar domínio customizado** (opcional):
   - Settings → Networking → Custom Domain