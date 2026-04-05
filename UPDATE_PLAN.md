# Plano de Atualização - Comunidade Amigos Leituras

## Visão Geral
Este documento apresenta um plano de evolução para o projeto, identificando melhorias, refatorações e novas funcionalidades baseadas na análise do código existente.

---

## 🔴 Problemas Críticos (Corrigir Imediatamente)

### 1. Imagens no Editor
- **Status**: Não estão alinhando corretamente ao texto
- **Causa**: CSS não está fazendo o float correto após sanitização
- **Solução**: Revisar CSS das imagens no editor e garantir que os atributos de alinhamento sejam preservados

### 2. Busca/Global
- **Status**: Não está funcionando corretamente
- **Causa**: Possível problema no endpoint ou no frontend
- **Solução**: Depurar o SearchController e o componente SearchBar

---

## 🟡 Melhorias de Qualidade de Código

### 1. Refatorar App.jsx (411 linhas)
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
-统一izar para português (ou inglês)的一致性

---

## 🟢 Novas Funcionalidades Sugeridas

### 1. Sistema de Comments Avançado
- [ ] Reply a comentários (aninhados)
- [ ] Editar próprio comentário
- [ ] Deletar próprio comentário
- [ ]法定 Reactions (like, love, etc)

### 2. Sistema de Moderation
- [ ] Report de conteúdo
- [ ] Pin de tópico (apenas moderadores)
- [ ] Close/Travamento de tópico

### 3. Gamificação Expandida
- [ ] Desafios semanais/mensais
- [ ] Sistema de níveis visuais no perfil
- [ ] Conquitas por categoria (leitura, discussão, etc)

### 4. Frontend Improvements
- [ ] Implementar TypeScript
- [ ] Adicionar Error Boundaries
- [ ] Lazy loading de componentes
- [ ] Melhorar Mobile UX

---

## 🔵 Melhorias de Segurança

### 1. Autenticação
- [ ] Rate limiting no login
- [ ] Melhorar mensagens de erro (não revelar email vs senha)
- [ ] tokens de acesso mais curtos

### 2. Proteções
- [ ] CSRF em endpoints que precisam (se necessário)
- [ ] sanitização server-side de conteúdo
- [ ] Limite de tamanho de upload

---

## 🟣 Arquitetura

### Curto Prazo (1-2 meses)
1. Corrigir bugs críticos (imagens, busca)
2. Limpeza de código (App.jsx, duplicatas)
3. Adicionar TypeScript gradualmente

### Médio Prazo (3-6 meses)
1. Implementar React Router
2. Expandir sistema de comments
3. Melhorias de gamificação

### Longo Prazo (6-12 meses)
1. PWA para mobile
2. Notificações push (Service Workers)
3. Moderação avançada

---

## 📋 Tarefas de Limpeza (Feitas)

- [x] Remover SearchService.php (duplicado)
- [x] Remover ExampleTest.php (placeholder)
- [x] Corrigir usuários endpoint (auth)
- [x]统一izar resposta TopicController

---

## Notas

O projeto está em estado funcional com 109 testes passando. A base é sólida, mas precisa de:
1. Refatoração do frontend (App.jsx)
2. Correção dos bugs de imagem/busca
3. Limpeza de código duplicado

O roadmap deve priorizar:
1. Correção dos bugs críticos
2. Refatoração para manutenção
3. Novas funcionalidades de interação