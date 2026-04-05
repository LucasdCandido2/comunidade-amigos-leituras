# 📋 TODO - Comunidade Amigos Leituras

## 🎯 MVP (Produto Mínimo Viável) - 100% CONCLUÍDO ✅🎉

### 1. Setup Inicial
- [x] Criar projeto Laravel 10 no container `app`  
- [x] Criar projeto React 18 (Vite) no container `frontend`  
- [x] Validar acesso local (http://localhost:8000 e http://localhost:3000)  

### 2. Domínio & Banco de Dados
- [x] Criar migrations para todas as entidades (User, Work, Topic, etc)  
- [x] Definir relacionamentos (Foreign Keys) no banco  
- [x] Criar seeders para cargos e permissões iniciais  
- [x] Criar seeder para usuário de teste  

### 3. Autenticação & RBAC
- [x] Implementar registro/login de usuários  
- [x] Criar middleware de autenticação  
- [x] Implementar sistema de Cargos e Permissões (RBAC)  
- [x] Criar middleware para controle de acesso baseado em permissões  
- [x] Atribuir cargo "Dono" ao usuário de teste  
- [x] Implementar Sanctum tokens (API Bearer)
- [x] Interceptor Axios para injeção automática de tokens
- [x] **Migração para Bearer token puro** (removido CSRF, EnsureFrontendRequestsAreStateful e chamadas desnecessárias)
- [x] **Middleware `BearerTokenAuth` aprimorado** — resolve usuário via `setUserResolver`, evita SIGSEGV no PHP-FPM
- [x] **Proxy Vite configurado** — zero CORS preflight, 1 requisição por ação
- [x] **PHP-FPM estabilizado** — OPcache desabilitado via `docker/php/php-dev.ini`
- [x] **Nginx com CORS fallback** — headers presentes mesmo em erros 5xx
- [x] **Cache de usuário no localStorage** — navegação pós-login imediata

### 4. Módulo de Tópicos & Obras
- [x] Criar Model e Migration para Obras (Works)  
- [x] CRUD de Tópicos (Backend API funcional)  
- [x] CRUD de Tópicos (Frontend componente CreateTopic)
- [x] Editor de texto rico (Integração Frontend)  
- [x] Sistema de interações/respostas (Backend implementado)
- [x] Sistema de interações/respostas (Interface Frontend para listar/criar)
- [x] Página de detalhes do tópico com comentários
- [x] Sistema de edição/deleção de tópicos (Frontend)

### 5. Ranking & Biblioteca
- [x] Implementar algoritmo de ranking bayesiano (Backend concluído)  
- [x] Endpoint `/works/top` para retornar 10 obras melhor rankeadas
- [x] Criar página de "Mais Acessados" e "Melhor Pontuados" (Frontend)  
- [x] Permitir cadastro de novas obras no momento da criação do tópico  
- [x] **CRUD completo de obras (Backend API funcional com testes TDD)**

### 6. Funcionalidades Avançadas (Próximas Fases)
- [x] CRUD completo de obras (Backend API funcional com testes TDD + validações de ownership)
- [x] Sistema de autenticação Bearer corrigido (sem CSRF, sem preflight, PHP-FPM estável)
- [x] CRUD completo de obras (criar/editar obras no frontend)
- [x] Upload de imagens/arquivos para tópicos (Backend + Frontend + testes TDD)
- [x] Sistema de edição/deleção de tópicos (frontend — inline edit + delete com ownership check)
- [x] Sistema de Notificações (Backend + API REST + geração automática de notificações)
- [x] Busca global (implementação com LIKE PostgreSQL)
- [x] Gamificação (badges, reputação, níveis, leaderboard)

### 7. Acervo & Storage (Futura Fase)
- [x] Integrar DigitalOcean Spaces (S3) para upload de arquivos (configuração preparada, disco 'spaces' disponível)
- [x] Implementar Signed URLs para acesso seguro
- [x] Criar área de download de arquivos no frontend (integrado em TopicDetail)
- [x] Compressão automática de imagens no upload (Intervention Image)  

---

## 🚀 Roadmap Pós-MVP

### Funcionalidades Avançadas
- [x] Gamificação (badges, reputação) ✅
- [x] Sistema de Notificações ✅
- [x] Busca global ✅
- [x] Signed URLs para arquivos ✅
- [x] Sistema de votação/cooldown timer ✅
- [x] Spoiler tag no editor de texto ✅
- [x] Diretório de fontes externas (wiki de sites) ✅
- [x] Exportação de tópicos como PDF ✅  

### Infra & Deploy
- [ ] Criar pipeline CI/CD no GitHub Actions  
- [ ] Configurar domínio personalizado e SSL  

### Melhorias de UX
- [x] Tema dark/light mode ✅
- [x] UI/UX reformulada com estilo WhatsApp para comentários ✅
- [x] Página inicial (Home) com estatísticas do usuário ✅
- [x] Página de perfil do usuário com badges e progresso ✅
- [x] Identificação visual de tópicos do usuário ✅
- [x] Login e Register estilizados com design system ✅
- [x] WorksRanking e WorkEditor estilizados com design system ✅
- [x] Editor rico TipTap com upload de imagens e text wrap ✅
- [x] Notificações em tempo real (Polling a cada 10s) ✅
- [x] Busca global avançada com highlighting ✅