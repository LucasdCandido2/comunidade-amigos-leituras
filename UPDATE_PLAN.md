# Plano de Atualização - Comunidade Amigos Leituras

## Visão Geral
Este documento apresenta um plano de evolução para o projeto, identificando melhorias, refatorações e novas funcionalidades baseadas na análise do código existente.

---

## 🔴 Problemas Críticos (Corrigir Imediatamente)

### 1. Performance do Frontend
- **Status**: Site demora para carregar
- **Causa**: 
  - Todos os componentes carregam juntos (monolítico)
  - Sem lazy loading
  - Múltiplas requisições simultâneas sem cache
- **Solução**: 
  - Implementar lazy loading com React.lazy
  - Adicionar memoização
  - Otimizar re-renders

### 2. Imagens no Editor
- **Status**: Não estão alinhando corretamente ao texto
- **Causa**: CSS não está fazendo o float correto após sanitização
- **Solução**: Revisar CSS das imagens no editor

---

## 🟡 Melhorias de Qualidade de Código

### 1. Refatorar App.jsx (412 linhas)
**Problema**: Monolítico com 20+ estados e 15+ handlers
**Proposta**: 
- Implementar React Router para navegação
- Separar em páginas/componentes menores
- Criar hooks customizados para lógica reutilizável

### 2. Limpar Componentes Duplicados
- **SpoilerContent** em TopicDetail.jsx - já existe SpoilerTag.jsx
- Unificar SpoilerTag para uso em todos os lugares

### 3. Padronizar Mensagens de Erro
- Mistura de português/inglês nos endpoints

---

## 🟢 Novas Funcionalidades Sugeridas

### 1. Sistema de Comments Avançado
- [ ] Reply a comentários (aninhados)
- [ ] Editar próprio comentário
- [ ] Deletar próprio comentário

### 2. Sistema de Moderação
- [ ] Report de conteúdo
- [ ] Pin de tópico (apenas moderadores)
- [ ] Close/Travamento de tópico

### 3. Gamificação Expandida
- [ ] Desafios semanais/mensais
- [ ] Sistema de níveis visuais no perfil
- [ ] Conquistas por categoria

---

## 🔵 Melhorias de Segurança

### 1. Autenticação
- [ ] Rate limiting no login
- [ ] Melhorar mensagens de erro

### 2. Proteções
- [ ] Sanitização server-side de conteúdo
- [ ] Limite de tamanho de upload

---

## 🚀 Plano de Execução - Performance

### Fase 1: Otimizações Imediatas (Agora)
- [x] Sistema de solicitações de cadastro ✅
- [x] Lazy loading de todos os componentes ✅
- [x] Memoização de TopicsList e computed values ✅
- [x] Arquitetura MVVM com useAppViewModel ✅
- [x] Corrigido bug: admin permanecia ativo ao clicar em início ✅
- [x] Cache de requisições axios (30s) ✅
- [x] Notificações: intervalo aumentado (30s) + loading only when open ✅
- [x] Backend: Cache Redis para tópicos (5min) ✅

### Fase 2: Refatoração (Próximas semanas)
- [ ] Implementar React Router para navegação
- [ ] Separar App.jsx em componentes menores
- [ ] Limpar componentes duplicados

### Fase 3: Novas Funcionalidades
- [ ] Sistema de replies em comentários
- [ ] Sistema de moderação
- [ ] Gamificação avançada

---

## 📋 Tarefas Concluídas

- [x] Sistema de solicitações de cadastro com aprovação admin
- [x] Seeders com 4 cargos padrão
- [x] UI/UX melhorada para visualização de permissões
- [x] Correções no fluxo de roles (many-to-many)
- [x] Reset showAdmin no logout
- [x] Lazy loading com React.lazy + Suspense
- [x] Memoização de TopicsList
- [x] Cache de requisições no axios
- [x] Arquitetura MVVM (useAppViewModel hook)
- [x] Corrigido bug admin permanece ativo ao voltar para início

---

## Próximas Otimizações Pendentes

1. React Router para navegação (substituir ViewState por rotas)
2. Separação de App.jsx em mais componentes
3. Lazy loading de componentes pesados (WorkEditor, RoleManagement)
4. Sistema de replies em comentários