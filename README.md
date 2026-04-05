# Comunidade Amigos Leituras

## 🌟 Visão Geral

Plataforma privada de compartilhamento de experiências (Leituras/Animes/Mangás/HQs) com foco em curadoria, debate e ranking inteligente. Um espaço seguro e colaborativo para amigos discutirem e preservarem conhecimento.

Cláusula Docker Compose Syntax: Todos os comandos Docker Compose devem utilizar a nova sintaxe docker compose (espaço) em vez da legada docker-compose (hífen). Isso garante compatibilidade com as versões mais recentes do Docker Desktop e CLI.

---

## 🧱 Arquitetura & Stack

- **Modelo:** Monorepo (Backend + Frontend).
- **Orquestração:** Docker Compose (Desenvolvimento e Produção).
- **Backend:** Laravel 10 (PHP 8.1) - API RESTful.
- **Frontend:** React 18 (Vite).
- **Banco de Dados:** PostgreSQL 15.
- **Cache/Sessão:** Redis.
- **Storage:** DigitalOcean Spaces (S3 Compatible) — em planejamento.
- **CI/CD:** GitHub Actions (focado em Docker Build/Push).

---

## 🎯 Status Atual
✅ Ambiente Docker Compose configurado e funcional  
✅ Projeto Laravel 10 e React 18 (Vite) rodando  
✅ Autenticação via Bearer Token (Login/Registro/Logout) funcional  
✅ Interceptor Axios para injeção automática de tokens  
✅ Sistema de RBAC (Cargos e Permissões) implementado e testado  
✅ **Módulo de Obras (Works):** Model, Migration, CRUD completo backend com testes TDD e Endpoint de ranking bayesiano  
✅ **Módulo de Tópicos (Topics):** CRUD completo via API + Frontend funcional  
✅ **Sistema de Interações (Comentários):** Backend implementado com cálculo de ranking + Interface Frontend completa  
✅ **Ranking Bayesiano:** Backend implementado, incluindo endpoint `/works/top`  
✅ **Página de Ranking:** Frontend completo com obras mais bem avaliadas  
✅ **Cadastro de Obras:** Funcionalidade integrada na criação de tópicos  
✅ **Upload de Arquivos:** Sistema completo de upload de imagens/PDF com drag-and-drop no Frontend + compressão automática  
✅ **Signed URLs:** URLs temporárias assinadas para compartilhamento seguro de arquivos (até 30 dias)  
✅ **Sistema de Notificações:** API REST completa para notificações automáticas (novos comentários, ratings)  
✅ **Busca Global:** Endpoint `/api/search` para buscar em tópicos e obras  
✅ **Gamificação:** Sistema de badges, reputação e níveis com leaderboard  
✅ **UI/UX Reformulada:** Página inicial com estatísticas, página de perfil do usuário com badges e progresso  
✅ **Estilo WhatsApp:** Comentários em formato de chat com bolhas diferenciadas para usuário atual  
✅ **Autenticação Bearer Token Pura:** CSRF removido, middleware customizado sem SIGSEGV  
✅ **Proxy Vite:** Zero CORS preflight — 1 requisição por ação  
✅ **PHP-FPM Estável:** OPcache desabilitado em dev, sem crashes SIGSEGV  
✅ **Testes:** 109 testes passando com cobertura de todas as funcionalidades  
✅ **Usuário de Teste:** `teste1234@teste.com` com senha `teste12345`
✅ **Obra de Teste:** "One Piece"  
✅ **Tópico de Teste:** "Meu primeiro tópico de teste"
✅ **Usuário de Teste:** `teste1234@teste.com` configurado como "Dono"

## 🎉 Implementações Recentes
- ✅ **Editor Rico TipTap**: Editor de texto avançado com upload de imagens (cola, arrasta, botão), text wrap, negrito, itálico, links e listas
- ✅ **Imagens Redimensionáveis**: Suporte a redimensionamento de imagens (25%, 50%, 75%, 100%) com alinhamento (esquerda, direita, centro) preservando proporções
- ✅ **Correção WebP**: Fallback automático para JPEG quando suporte WebP não está disponível no PHP
- ✅ **Feedback Visual**: Spinner de upload indicando progresso durante envio de imagens
- ✅ **Spoiler Tag**: Sistema de marcação de spoilers no editor com sintaxe `[spoiler]texto[/spoiler]`, componente visual com blur e revelação por clique
- ✅ **Diretório de Fontes Externas**: Nova página Wiki para gerenciar links úteis organizados por tipo (wiki, site, forum, store), com CRUD completo
- ✅ **Exportação de PDF**: Endpoint público `GET /api/topics/{id}/pdf` para exportar tópico completo com comentários usando DOMPDF
- ✅ **Notificações em Tempo Real**: Sistema de polling a cada 10s com sino de notificações, browser notifications e dropdown com lista de notificações
- ✅ **Busca Global Avançada**: Autocomplete na navbar com highlighting, relevância, snippets de conteúdo e filtros por tipo
- ✅ **Refatoração UI/UX Completa**: Nova página inicial (Home) com estatísticas do usuário, badges e ações rápidas; página de perfil do usuário com progresso de nível e badges desbloqueados
- ✅ **Signed URLs para Arquivos**: URLs temporárias assinadas com expiração configurável (1min a 30 dias),HMAC-SHA256 para segurança, downloads públicos sem autenticação
- ✅ **Sistema de Gamificação Completo**: Badges (Primeiro Passo, Leitor Ativo, Comentador, etc), reputação por ações, níveis (Novato até Lenda), e leaderboard público
- ✅ **Sistema de Busca Global**: Endpoint `/api/search` com filtros por tipo (works/topics), busca case-insensitive em títulos e descrições
- ✅ **Sistema de Notificações**: Geração automática de notificações ao criar tópicos, comentários e avaliações; API REST completa com mark-as-read
- ✅ **Upload de Arquivos**: Módulo completo de upload de imagens/PDF com drag-and-drop, compressão automática via Intervention Image, validação de tipos
- ✅ **Correção Definitiva de CORS/Auth**: Migração para Bearer token puro — removidos CSRF cookies, `EnsureFrontendRequestsAreStateful` e chamadas desnecessárias a `/sanctum/csrf-cookie`
- ✅ **Proxy Vite para Zero Preflight**: Frontend usa URL relativa `/api`, Vite encaminha internamente — browser enxerga mesma origem, sem OPTIONS preflight (1 req/ação)
- ✅ **Middleware `BearerTokenAuth` Aprimorado**: Substitui `auth:sanctum` com resolução via `setUserResolver` — evita SIGSEGV no PHP-FPM e mantém `$request->user()` funcional
- ✅ **PHP-FPM Estabilizado**: OPcache desabilitado via `docker/php/php-dev.ini` — elimina crashes SIGSEGV que causavam 502 Bad Gateway
- ✅ **Nginx com CORS Fallback**: `add_header` com `always` no bloco PHP — CORS presente mesmo em respostas de erro 5xx
- ✅ **Cache de Usuário no Frontend**: Login/register salvam user no `localStorage` — navegação pós-login imediata sem requisição extra a `/api/user`
- ✅ **Testes TDD**: 109 testes cobrindo todas as funcionalidades principais (AssetUpload, Notifications, Search, Gamification, SignedUrls, Votes)

---

## 🎯 MVP: Domínio & DDD

### Entidades Principais

- **User**: [Concluído] Autenticação com Sanctum e Roles.  
- **Work**: [Concluído] Model, Migration e Endpoint de ranking bayesiano.  
- **Topic**: [Concluído] CRUD completo via API + Frontend funcional.  
- **Interaction**: [Concluído] Sistema de comentários com ratings e interface completa.  
- **Asset**: Arquivo (PDF, imagem) vinculado a um tópico. [Planejado]
- **Role**: [Concluído] Cargo com permissões.  
- **Permission**: [Concluído] Permissão granular.

### Agregados

- **User Aggregate**: Usuário e seus relacionamentos.  
- **Topic Aggregate**: Tópico, obra, interações e arquivos.  
- **Work Aggregate**: Obra e seus tópicos associados.

### Serviços de Domínio

- **RankingService**: Calcula o score bayesiano.  
- **AuthorizationService**: Verifica permissões RBAC.  
- **StorageService**: Gerencia upload/download no Spaces.

## 🚀 Próximos Passos
1. ✅ Implementar a integração do **Frontend (React)** com as APIs de Tópicos e Interações.  
2. ✅ Desenvolver o CRUD completo para **Works** (Obras) no backend e frontend.  
3. ✅ Corrigir sistema de autenticação Sanctum (Bearer tokens + CSRF cookies).  
4. Implementar o **frontend CRUD de obras** (componente WorkEditor para criar/editar obras).  
5. Iniciar a integração do sistema de upload de imagens no frontend.

---

## 🛡️ Regras de Negócio

1. **Governança de Cargos (RBAC)**  
   - Cargo "Dono" imutável.  
   - Cargos personalizáveis definidos coletivamente.  
2. **Moderação Inteligente**  
   - Auto-moderation via votos.  
   - Shadow Ban para usuários problemáticos.  
3. **Qualidade de Conteúdo**  
   - Tópicos vinculados a obras da biblioteca.  
   - Editor rico com suporte a spoiler.  
4. **Privacidade & Segurança**  
   - Acesso somente por convite.  
   - Signed URLs para mídia.  
5. **Preservação de Conhecimento**  
   - Acervo comunitário moderado.  
   - Diretório de fontes confiáveis.

---

## 📁 Estrutura do Projeto (Monorepo)

```
/comunidade-amigos-leituras
├── backend/ # Laravel 10 API
├── frontend/ # React 18 App
├── docker/ # Definições de Ambiente
│ ├── php/ # Dockerfile PHP-FPM
│ ├── nginx/ # Configurações Nginx
│ └── frontend/ # Dockerfile Node.js (Vite)
├── docker-compose.yml # Orquestrador Único do Projeto
├── .github/ # Workflows de CI/CD
├── README.md # Documentação
└── TODO.md # Gestão de tarefas
```

---
## 📦 Componentes & Serviços Implementados

### Frontend - Componentes React
- **Login.jsx** - Formulário de autenticação (estilizado)
- **Register.jsx** - Formulário de registro (estilizado)
- **CreateTopic.jsx** - Formulário para criar novos tópicos (estilizado com tokens CSS)
- **TopicDetail.jsx** - Página de detalhes com estilo WhatsApp para comentários
- **Home.jsx** - Página inicial com estatísticas e atividades recentes
- **UserProfile.jsx** - Perfil do usuário com badges, progresso e tópicos
- **WorksRanking.jsx** - Ranking de obras (estilizado)
- **WorkEditor.jsx** - CRUD de obras (estilizado)
- **WikiSources.jsx** - Diretório de fontes externas por categoria
- **SpoilerTag.jsx** - Componente para marcar e revelar spoilers
- **RichTextEditor.jsx** - Editor rico TipTap com upload de imagens
- **SearchBar.jsx** - Busca com autocomplete e highlighting
- **NotificationBell.jsx** - Sino de notificações com dropdown
- **ThemeToggle.jsx** - Toggle dark/light mode
- **App.jsx** - Component principal com gerenciamento de usuário, autenticação e navegação

### Frontend - Services Axios
- **authService.js** - Métodos: `register()`, `login()`, `logout()`, `getUser()`
- **topicService.js** - Métodos: `getAll()`, `getById()`, `create()`, `update()`, `delete()`
- **workService.js** - Métodos: `getAll()` (retorna top 10 obras rankeadas)
- **interactionService.js** - Métodos: `create()` (criar comentários em tópicos)
- **assetService.js** - Métodos: `upload()`, `getByTopic()`, `delete()`
- **gamificationService.js** - Métodos: `getStats()`, `getLeaderboard()`, `getBadges()`
- **externalSourceService.js** - Métodos: `getAll()`, `create()`, `delete()`
- **searchService.js** - Métodos: `search()` com highlighting e filtros
- **notificationService.js** - Métodos: `getAll()`, `markAsRead()`, `markAllAsRead()`, `delete()`
- **api.js** - Configuração Axios base com interceptor de token automático

### Backend - Controllers
- **AuthController** - Sanctum: `register()`, `login()`, `logout()`, `user()`
- **TopicController** - CRUD completo: `index()`, `store()`, `show()`, `update()`, `destroy()`
- **WorkController** (`Controllers/Api/WorkController.php`) - CRUD completo com ranking bayesiano
- **InteractionController** (`Controllers/Api/InteractionController.php`) - `store()` cria comentários e recalcula ranking
- **VoteController** (`Controllers/Api/VoteController.php`) - `vote()` para upvotes/downvotes com cooldown, `getVoteStatus()` para consultar status
- **ExternalSourceController** (`Controllers/Api/ExternalSourceController.php`) - CRUD de fontes externas
- **PdfController** (`Controllers/Api/PdfController.php`) - Exportação de tópicos como PDF

### Backend - Services
- **RankingService** - Calcula score bayesiano para obras com base em ratings
- **VoteService** - Gerencia votações (upvote/downvote) com cooldown de 60s via Cache
- **AdvancedSearchService** - Busca avançada com highlighting, relevância e filtros
- **PdfService** - Geração de PDFs com DOMPDF

### Frontend - Hooks
- **useNotifications** - Hook para polling de notificações com toast
- **useNotificationPermission** - Hook para gerenciar permissão de browser notifications

---
## � Autenticação & Tokens

### Fluxo de Autenticação (Sanctum + JWT)

1. **Login**: POST `/api/login` com email/senha
2. **Resposta**: Backend retorna `{user, token}` onde token é um token Sanctum
3. **Armazenagem**: Frontend armazena token em `localStorage`
4. **Requisições Protegidas**: Interceptor Axios injeta `Authorization: Bearer {token}` automaticamente
5. **Middleware**: Backend valida token com `auth:sanctum` nas rotas protegidas
### Controle de Acesso (RBAC)

- **Cargos Disponíveis**: "Dono", "Moderador", "Membro"
- **Permissões**: `create_topic`, `edit_any_topic`, `delete_any_topic`, etc.
- **Middleware**: `RbacMiddleware` valida permissões por rota
- **Usuário de Teste**: `lucas1234@teste.com` tem cargo "Dono" (acesso completo)
### Testando a Autenticação

```bash
# 1. Obter CSRF token
curl -X GET http://localhost:8000/api/sanctum/csrf-cookie \
  -H "Accept: application/json"

# 2. Fazer login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"lucas1234@teste.com","password":"lucas12345"}'

# 3. Usar token em requisições protegidas
curl -X GET http://localhost:8000/api/topics \
  -H "Authorization: Bearer {seu_token_aqui}"
```

---

## �🚀 Próximos Passos

### **Phase 1 - Frontend Interações** (Concluído ✅)
```
✅ Componente para exibir comentários em tópico (TopicDetail.jsx)
✅ Componente para criar novo comentário (integrado no TopicDetail)
✅ Página de detalhes do tópico com comentários
✅ Integração com interactionService
✅ Endpoint GET /topics/{id}/interactions implementado
```

### **Phase 2 - Funcionalidades de Edição** (Próxima Prioridade)
```
[ ] Sistema de edição de tópicos (Frontend)
[ ] Sistema de deleção de tópicos (Frontend)
[ ] Validação de permissões (só dono pode editar/deletar)
```
[ ] Filtros e ordenação
```

### **Phase 3 - Funcionalidades Avançadas**
```
[ ] CRUD completo de obras (criar/editar obras)
[ ] Sistema de edição/deleção de tópicos
[ ] Upload de imagens/arquivos
[ ] Notificações em tempo real
```

Consulte o arquivo `TODO.md` para a lista completa de tarefas priorizadas.
