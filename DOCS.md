# Documentação do Sistema - Comunidade Amigos Leituras

## Sumário

1. [Visão Geral](#visão-geral)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Arquitetura](#arquitetura)
4. [Instalação e Configuração](#instalação-e-configuração)
5. [Funcionalidades Implementadas](#funcionalidades-implementadas)
6. [API Endpoints](#api-endpoints)
7. [Frontend - Componentes](#frontend---componentes)
8. [Testes](#testes)
9. [Variáveis de Ambiente](#variáveis-de-ambiente)
10. [Segurança](#segurança)

---

## Visão Geral

Plataforma privada de compartilhamento de experiências (Leituras/Animes/Mangás/HQs) com foco em curadoria, debate e ranking inteligente. Um espaço seguro e colaborativo para amigos discutirem e preservarem conhecimento.

**Usuário de Teste**: `teste1234@teste.com` / `teste12345`

---

## Stack Tecnológico

| Componente | Tecnologia | Versão |
|------------|------------|--------|
| Backend | Laravel | 10.x |
| Frontend | React | 18.x |
| Build Tool | Vite | 5.x |
| Database | PostgreSQL | 15 |
| Cache/Session | Redis | 7.x |
| Servidor Web | Nginx | Alpine |
| Container | Docker Compose | 2.x |
| Testes | PHPUnit | 10.x |

---

## Arquitetura

### Monorepo Structure

```
/comunidade-amigos-leituras
├── backend/                 # Laravel 10 API
│   ├── app/
│   │   ├── Http/           # Controllers, Middleware
│   │   ├── Models/         # Eloquent Models
│   │   ├── Services/       # Business Logic
│   │   └── Providers/      # Service Providers
│   ├── database/           # Migrations, Seeders, Factories
│   ├── routes/             # API Routes
│   └── tests/              # Feature & Unit Tests
├── frontend/               # React 18 App
│   ├── src/
│   │   ├── components/    # React Components
│   │   ├── services/      # API Services (Axios)
│   │   ├── hooks/         # Custom Hooks
│   │   └── styles/        # CSS Tokens & Components
│   └── __tests__/         # Frontend Tests
├── docker/                 # Docker configurations
│   ├── php/               # PHP-FPM Dockerfile
│   ├── nginx/             # Nginx config
│   └── frontend/          # Node.js Dockerfile
├── docker-compose.yml      # Development orchestration
└── docker-compose.prod.yml # Production configuration
```

### Fluxo de Requisição

```
Browser → Vite Proxy → Nginx → PHP-FPM → Laravel → PostgreSQL
                                      ↓
                                   Redis (Cache/Session)
```

---

## Instalação e Configuração

### Desenvolvimento Local

```bash
# 1. Clonar repositório
git clone https://github.com/usuario/comunidade-amigos-leituras.git
cd comunidade-amigos-leituras

# 2. Iniciar containers
docker-compose up -d

# 3. Executar migrações
docker exec comunidade_app php /var/www/artisan migrate

# 4. Executar seeders
docker exec comunidade_app php /var/www/artisan db:seed

# 5. Acessar
# Frontend: http://localhost:3000
# Backend:  http://localhost:8000/api
```

### Variáveis de Ambiente

Criar arquivo `backend/.env`:

```env
APP_NAME=ComunidadeAmigosLeituras
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=comunidade_db
DB_USERNAME=user_admin
DB_PASSWORD=password_secret
REDIS_HOST=redis
FRONTEND_URL=http://localhost:3000
```

---

## Funcionalidades Implementadas

### ✅ Autenticação
- Registro de usuários
- Login com email/senha
- Logout
- Bearer Token (Sanctum)
- Cache de usuário no localStorage

### ✅ Sistema RBAC
- Cargos: Dono, Moderador, Membro
- Permissões granulares
- Middleware de verificação

### ✅ Works (Obras)
- CRUD completo
- Ranking bayesiano
- Tipos: book, manga, anime, comic, hq

### ✅ Topics (Tópicos)
- CRUD completo
- Vinculação a Works
- Rich Text Editor (TipTap)
- Sistema de spoiler tags

### ✅ Interactions (Comentários)
- Criar comentários em tópicos
- Estilo WhatsApp (chat bubbles)
- Ratings (1-5 estrelas)

### ✅ Votação
- Upvote/Downvote
- Cooldown de 60 segundos
- Cache de verificação

### ✅ Assets (Arquivos)
- Upload de imagens/PDF
- Compressão automática
- Inline images no editor

### ✅ Gamificação
- Badges (Primeiro Passo, Leitor Ativo, etc)
- Reputação por ações
- Níveis (Novato → Lenda)
- Leaderboard público

### ✅ Notificações
- Automáticas em novas interações
- Polling a cada 10 segundos
- Browser notifications

### ✅ Busca
- Busca global em tópicos e obras
- Filtros por tipo
- Highlighting de resultados

### ✅ Exportação
- PDF de tópicos
- Signed URLs para arquivos

---

## API Endpoints

### Autenticação
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/register` | Criar conta |
| POST | `/api/login` | Login |
| POST | `/api/logout` | Logout |
| GET | `/api/user` | Dados do usuário |

### Works
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/works` | Listar obras (top 10 por rating) |
| GET | `/api/works/top` | Top 10 obras |
| POST | `/api/works` | Criar obra |
| GET | `/api/works/{id}` | Ver obra |
| PUT | `/api/works/{id}` | Editar obra |
| DELETE | `/api/works/{id}` | Deletar obra |

### Topics
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/topics` | Listar tópicos |
| POST | `/api/topics` | Criar tópico |
| GET | `/api/topics/{id}` | Ver tópico |
| PUT | `/api/topics/{id}` | Editar tópico |
| DELETE | `/api/topics/{id}` | Deletar tópico |

### Interactions
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/topics/{id}/interactions` | Listar comentários |
| POST | `/api/topics/{id}/interactions` | Criar comentário |

### Assets
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/assets` | Upload de arquivo |
| POST | `/api/assets/upload-inline` | Upload de imagem inline |
| GET | `/api/assets/{id}` | Baixar arquivo |
| DELETE | `/api/assets/{id}` | Deletar arquivo |

### Gamificação
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/gamification/stats` | Estatísticas do usuário |
| GET | `/api/leaderboard` | Leaderboard |
| GET | `/api/badges` | Lista de badges |

### Busca
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/search?q=termo` | Busca global |

### Votação
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/vote/{type}/{id}` | Votar (up/down) |
| GET | `/api/vote/{type}/{id}/status` | Status da votação |

---

## Frontend - Componentes

### Pages
- `Home.jsx` - Dashboard com estatísticas
- `Login.jsx` / `Register.jsx` - Autenticação
- `WorksRanking.jsx` - Ranking de obras
- `WikiSources.jsx` - Fontes externas

### Components
- `CreateTopic.jsx` - Criar tópico
- `TopicDetail.jsx` - Ver tópico + comentários
- `WorkEditor.jsx` - CRUD de obras
- `RichTextEditor.jsx` - Editor rico com imagens
- `NotificationBell.jsx` - Notificações
- `SearchBar.jsx` - Busca com autocomplete
- `ThemeToggle.jsx` - Dark/Light mode
- `SpoilerTag.jsx` - Tags de spoiler

### Services
- `authService.js` - Autenticação
- `topicService.js` - Tópicos
- `workService.js` - Obras
- `interactionService.js` - Comentários
- `assetService.js` - Arquivos
- `gamificationService.js` - Gamificação
- `searchService.js` - Busca
- `notificationService.js` - Notificações
- `api.js` - Config Axios + interceptor

---

## Testes

### Backend (PHPUnit)

```bash
# Todos os testes
docker exec comunidade_app php /var/www/artisan test

# Testes específicos
docker exec comunidade_app php /var/www/artisan test --filter=AssetUploadTest
```

### Cobertura

| Módulo | Testes |
|--------|--------|
| AssetUpload | 17 |
| TopicCRUD | 19 |
| WorkCRUD | 19 |
| Vote | 12 |
| Search | 8 |
| Gamification | 7 |
| Notification | 9 |
| SignedUrl | 11 |
| Example | 2 |
| **Total** | **109** |

---

## Variáveis de Ambiente

### Backend (.env)

| Variável | Descrição | Padrão |
|----------|-----------|--------|
| `APP_NAME` | Nome do app | ComunidadeAmigosLeituras |
| `APP_ENV` | Ambiente | local |
| `APP_DEBUG` | Debug | true |
| `APP_KEY` | Chave Laravel | - |
| `DB_CONNECTION` | driver | pgsql |
| `DB_HOST` | host | db |
| `DB_PORT` | porta | 5432 |
| `DB_DATABASE` | nome | comunidade_db |
| `DB_USERNAME` | usuário | user_admin |
| `DB_PASSWORD` | senha | password_secret |
| `REDIS_HOST` | host | redis |
| `FRONTEND_URL` | URL frontend | http://localhost:3000 |

---

## Segurança

### Autenticação
- Bearer Tokens (Laravel Sanctum)
- Tokens com expiração
- Regeneração em login

### Autorização
- RBAC com permissões granulares
- Verificação de ownership em edições
- Middleware customizado

### Headers
- CORS configurado
- CSRF desabilitado (API only)
- X-Frame-Options: DENY

### Dados
- Hash de senhas (bcrypt)
- Prepared statements (Eloquent)
- Validação de input

---

## Troubleshooting

### Problema: 401 ao fazer requests
**Solução**: Verificar se o token está sendo enviado no header Authorization

### Problema: 500 ao fazer upload de imagem
**Solução**: Verificar logs em `docker logs comunidade_app`

### Problema: PostgreSQL não conecta
**Solução**: Verificar se o container `db` está rodando

### Problema: Frontend não carrega
**Solução**: Limpar cache com `npm run build` novamente

---

## Comandos Úteis

```bash
# Reiniciar containers
docker-compose restart

# Ver logs
docker-compose logs -f

# Executar comandos no container
docker exec -it comunidade_app bash

# Limpar cache Laravel
docker exec comunidade_app php /var/www/artisan cache:clear

# Executar migrações
docker exec comunidade_app php /var/www/artisan migrate

# Ver rotas
docker exec comunidade_app php /var/www/artisan route:list
```