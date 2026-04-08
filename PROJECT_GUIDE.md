# 📘 Guia do Projeto - Comunidade Amigos Leituras

> Este documento consolidado substitui todos os arquivos MD do projeto. Use-o como referência principal.

---

## 🎯 Visão Geral

**Plataforma privada** de compartilhamento de experiências (Leituras/Animes/Mangás/HQs) com foco em curadoria, debate e ranking inteligente.

- **Stack**: Laravel 10 + React 18 + PostgreSQL 15 + Redis + Docker
- **Autenticação**: Bearer Token (sem CSRF)
- **Testes**: 108+ testes passando

---

## 🚀 Começando

### Credenciais de Teste
```
Email: teste1234@teste.com
Senha: teste12345
Cargo: Dono (owner)
```

### URLs
- Backend: http://localhost:8000
- Frontend: http://localhost:3000

---

## 📋 Checklist de Desenvolvimento (Sempre Executar)

Antes de finalizar qualquer trabalho:

### 1. Testes
```bash
docker exec comunidade_app php artisan test
```

### 2. Verificação de Código
```bash
# PHP syntax
docker exec comunidade_app php -l backend/app

# Frontend lint
docker-compose exec frontend npm run lint
```

### 3. Banco de Dados
```bash
# Verificar migrations pendentes
docker exec comunidade_app php artisan migrate:status

# Rodar migrations
docker exec comunidade_app php artisan migrate

# Rodar seeders
docker exec comunidade_app php artisan db:seed
```

### 4. Containers
```bash
docker-compose ps
```

---

## 🛠️ Comandos Úteis

### Setup Completo (Recomendado)
```bash
# Executa migrations, seeders e popula ~200 obras (usuário teste + anime + manga + movies)
php artisan db:seed --force
```

### Verificar Ambiente de Teste
```bash
# Verifica se usuário teste e obras estão configurados
php artisan test:setup
```

### Seeders Individuais
```bash
php artisan db:seed --class=RolePermissionSeeder   # Cargos e permissões
php artisan db:seed --class=ExternalSourceSeeder   # Fontes externas
php artisan db:seed --class=CategorySeeder         # Categorias de obras
php artisan db:seed --class=TestDataSeeder         # Usuário teste + dados + obras populadas
```

### Popular Obras via API
> ⚠️ **Nota**: AniList e Kitsu podem ter problemas de rate limiting ou API changes

```bash
# Anime (Jikan) - ~100 obras
php artisan works:populate --source=jikan --type=anime --limit=10

# Manga (Jikan) - ~100 obras
php artisan works:populate --source=jikan --type=manga --limit=10

# Movies (OMDB) - funciona reliably
php artisan works:populate --source=omdb --type=movie --limit=20
```

---

## 🔌 APIs Externas

### Status Atual

| API | Status | Conteúdo | Notas |
|-----|--------|----------|-------|
| Jikan | ✅ Funcionando | Anime/Manga | Sem API key |
| AniList | ✅ Funcionando | Anime/Manga | Sem API key, GraphQL |
| OMDB | ✅ Funcionando | Movies | API key configurada no .env |
| Kitsu | ❌ Problema | - | Retorna 404 (API mudou) |
| TMDB | ❌ Precisa Key | Movies/TV | Solicitar em themoviedb.org |
| ComicVine | ❌ Precisa Key | Comics | Solicitar approval |

### Configuração de API Keys

Adicionar no arquivo `backend/.env`:
```env
OMDB_API_KEY=sua_chave_aqui
TMDB_API_KEY=sua_chave_aqui
COMICVINE_API_KEY=sua_chave_aqui
```

---

## 🌐 Novos Endpoints API

### Categorias (Público)
- `GET /categories` - Lista todas as categorias
- `GET /categories/{id}/works` - Lista obras por categoria

### Filtro A-Z (Público)
- `GET /works/letters` - Letras disponíveis com contagem
- `GET /works/letter/{letter}` - Obras por letra inicial
- `GET /works/letter/{letter}?type=anime` - Filtro por tipo

### Categorias (Autenticado)
- `POST /works/{id}/categories` - Associar categorias a uma obra

---

## 📁 Estrutura do Projeto

```
comunidade-amigos-leituras/
├── backend/                 # Laravel 10 API
│   ├── app/
│   │   ├── Http/           # Controllers, Middleware
│   │   ├── Models/         # Eloquent Models
│   │   │   ├── Work.php
│   │   │   └── Category.php  # NOVO
│   │   ├── Services/       # Business Logic
│   │   └── Console/Commands # Artisan Commands
│   ├── database/
│   │   ├── migrations/    # Inclui categories
│   │   └── seeders/       # Inclui CategorySeeder
│   ├── routes/
│   └── tests/
├── frontend/               # React 18 (Vite)
│   ├── src/
│   │   ├── components/    # Inclui WorksByLetter
│   │   ├── services/      # workService.js atualizado
│   │   ├── hooks/         # Custom Hooks
│   │   └── styles/
│   └── package.json
├── docker-compose.yml
├── PROJECT_GUIDE.md        # Este arquivo
└── *.md                   # Arquivos legados (deprecados)
```

---

## 🎨 Padrões de Código

### PHP/Laravel
- PSR-12 para formatação
- Nomes de classes: PascalCase
- Métodos/variáveis: camelCase
- Usar type hints onde possível
- Documentar funções complexas

### React/JavaScript
- Usar Hooks (useState, useEffect, etc)
- Componentes funcionais
- Nomes de componentes: PascalCase
- Nomes de funções/variáveis: camelCase

### CSS
- Usar variáveis CSS do design system
- Seguir convenções BEM para classes
- Manter consistência com `components.css`

---

## ✅ Funcionalidades Implementadas

### Core
- [x] Autenticação Bearer Token (sem CSRF)
- [x] RBAC (Cargos e Permissões)
- [x] CRUD de Obras (Works)
- [x] CRUD de Tópicos (Topics)
- [x] Sistema de Interações/Comentários
- [x] Ranking Bayesiano
- [x] Upload de Arquivos com compressão
- [x] Signed URLs para downloads seguros
- [x] Sistema de Notificações
- [x] Busca Global
- [x] Gamificação (badges, reputação, níveis)
- [x] Editor Rico (TipTap)
- [x] Spoiler Tags
- [x] Exportação PDF de tópicos
- [x] Tema Dark/Light

### APIs Externas
- [x] Integração Jikan (anime/manga)
- [x] Integração AniList (anime/manga)
- [x] Integração OMDB (movies)
- [x] Comando `works:populate` para popularização

### Novas Funcionalidades (2026-04-06)
- [x] Sistema de Categorias para Obras (many-to-many)
- [x] Filtro A-Z para Obras
- [x] Logs de tempo de requisição (Laravel)
- [x] Controle de visibilidade Admin por cargo

## 📋 Tarefas Pendentes ( backlog)

### 🔴 Prioridade Alta

Nenhuma tarefa pendente no momento - todas concluídas!

---

## 📊 Dados Atuais

- **Usuário teste**: teste1234@teste.com / teste12345 (cargo: owner)
- **Anime**: ~100 obras (Jikan API)
- **Manga**: ~100 obras (Jikan API)
- **Movies**: ~94 obras (OMDB API)
- **Categorias**: 18 categorias
- **Total**: ~295 obras

---

## 🔧 Troubleshooting

### Problema: Tests falham
**Solução**: Verificar rate limiting
```php
// backend/app/Http/Kernel.php
'api' => ['throttle:300,1', ...]
```

### Problema: PHP-FPM crashes (SIGSEGV)
**Solução**: OPcache já está desabilitado no container dev

### Problema: CORS errors
**Solução**: Proxy Vite configurado - usar `/api` como caminho relativo

---

## 📚 Referências

- [Jikan API](https://jikan.moe/)
- [AniList GraphQL](https://anilist.co/graphiql)
- [OMDB API](http://www.omdbapi.com/)
- [Kitsu Docs](https://kitsu.docs.apiary.io/)
- [TMDB API](https://developers.themoviedb.org/)

---

## 📝 Notas de Desenvolvimento

1. **Fluxo de Trabalho**:
   - Antes de começar: Entender o problema, verificar código existente
   - Durante: Fazer pequenas alterações testáveis
   - Antes de finalize: Executar checklist obrigatório

2. **Testes**: Sempre rodar antes de commitar

3. **Docker**: Usar `docker compose` (não `docker-compose`)

4. **Frontend**: Proxy Vite remove necessidade de CORS

5. **Monitoramento de Performance**:
   - Logs Laravel registram tempo de requisição em `storage/logs/laravel.log`
   - Formato: method, path, user_id, duration_ms, status_code
   - Use a aba Network do navegador para complementar análise

---

*Última atualização: 2026-04-06*
*Este documento substitui: AGENTS.md, TODO.md, API_CONFIG.md, EXTERNAL_SOURCES.md, DOCS.md*