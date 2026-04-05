# 📋 Checklist de Pré-Implementação - Funcionalidades Avançadas

## Processo TDD para Cada Funcionalidade

### Ciclo: 🔴 RED → 🟢 GREEN → 🔵 REFACTOR

```
1. 🔴 RED: Escrever testes (falham porque código não existe)
2. 🟢 GREEN: Implementar código mínimo para passar nos testes
3. 🔵 REFACTOR: Melhorar código mantendo testes passando
4. ✅ DEPLOY: Merge para main quando tudo passa e cobertura > 80%
```

---

## 1. CRUD Completo de Obras ✅ PRONTO PARA IMPLEMENTAÇÃO

### ✅ Análise Completa
- [x] Problemas identificados
- [x] Casos de teste escritos (19 testes)
- [x] Critérios de aceitação documentados

### 📝 Tarefas de Implementação

#### Backend (Laravel)

```
[ ] Criar método update() no WorkController
    - Validar propriedade (é criador ou admin?)
    - Validar dados com as regras descritas no teste
    - Retornar 403 se sem permissão
    - Retornar 404 se work não existe

[ ] Criar método destroy() no WorkController
    - Soft delete (usar SoftDeletes)
    - Validar propriedade (403)
    - Retornar 404 se não existe

[ ] Adicionar rota PUT /api/works/{id}
[ ] Adicionar rota DELETE /api/works/{id}

[ ] Adicionar permissões:
    - Middleware de ownership check
    - Ou policy de Work

[ ] Rodar testes: php artisan test tests/Feature/WorkCRUDTest.php
    - Todos 19 testes devem passar
```

#### Frontend (React)

```
[ ] Criar componente WorkEditor.jsx
    - Props: work (para edit), onSave, onDelete

[ ] Implementar:
    - Form com campos pré-preenchidos (edit mode)
    - Validação de campos
    - Botão Salvar (PUT)
    - Botão Deletar com confirmação

[ ] Adicionar workService.update(id, data)
    - PUT request

[ ] Adicionar workService.delete(id)
    - DELETE request

[ ] Integrar no WorksRanking:
    - Botão "Editar" ao lado de cada obra

[ ] Integrar no CreateTopic:
    - Opção de editar obra existente

[ ] Rodar testes: npm test -- workService.test.js
    - Todos testes devem passar
```

### 🧪 Testes a Rodar

**Backend:**
```bash
cd backend
php artisan test tests/Feature/WorkCRUDTest.php

# Esperado: 19 tests passed
```

**Frontend:**
```bash
cd frontend
npm test -- workService.test.js

# Esperado: 14+ tests passed
```

### ✅ Definição de Pronto (DoD)

- [ ] Testes unitários passando (19 backend + 14+ frontend)
- [ ] Cobertura de código > 80%
- [ ] Code review aprovado
- [ ] Não quebra funcionalidades existentes
- [ ] Documentação no README atualizada

---

## 2. Edição/Deleção de Tópicos ✅ PRONTO PARA IMPLEMENTAÇÃO

### ✅ Análise Completa
- [x] Problemas identificados
- [x] Casos de teste documentados
- [x] Critérios de aceitação no TESTING_STRATEGY.md

### 📝 Tarefas de Implementação

#### Backend (Laravel)

```
[ ] Criar método update() no TopicController
    - Validar ownership
    - Validação de dados
    - Retornar 403 se sem permissão

[ ] Criar método destroy() no TopicController
    - Soft delete
    - Preservar comentários

[ ] Adicionar rotas PUT e DELETE

[ ] Rodar testes: php artisan test tests/Feature/TopicCRUDTest.php
```

#### Frontend (React)

```
[ ] Criar componente TopicEditor.jsx
    - Edit mode com form pré-preenchido
    - Validação
    - Delete com confirmação

[ ] Integrar em TopicDetail.jsx
    - Mostrar botões "Editar" e "Deletar" se é criador

[ ] topicService.update(id, data)
[ ] topicService.delete(id)

[ ] Rodar testes: npm test -- topicService.test.js
```

### 🧪 Testes a Rodar

```bash
# Backend
php artisan test tests/Feature/TopicCRUDTest.php

# Frontend
npm test -- topicService.test.js
```

---

## 3. Upload de Imagens ⚠️ PREPARANDO

### 📋 Antes de Implementar - Análise de Risco CRÍTICO

#### ⚠️ Problemas Críticos a Resolver

```
[ ] Definir tamanho máximo de arquivo
    - Sugestão: 5MB por arquivo
    - Máx 10 arquivos por tópico

[ ] Definir tipos de arquivo permitidos
    - Imagens: jpg, png, webp (MIME validation)
    - Documentos: pdf (opcional)

[ ] Configurar DigitalOcean Spaces
    - Criar bucket
    - Gerar credentials
    - Configurar CORS

[ ] Definir estratégia de segurança
    - Scan antivírus? (ClamAV)
    - Compressão de imagem?
    - Path traversal prevention?

[ ] Decidir sobre signed URLs
    - Duração de expiração
    - Como renovar links antigos
```

#### 🧪 Testes Necessários

```
[ ] Escrever tests/Feature/FileUploadTest.php
    - Upload válido
    - Rejeita arquivo grande
    - Rejeita tipo inválido
    - Gera URL signed
    - URL expira corretamente

[ ] Escrever frontend tests/components/FileUpload.test.jsx
    - Preview antes de upload
    - Barra de progresso
    - Validação de tipo
    - Handling de erro
```

### 📝 Próximas Ações

```
[ ] Pesquisar configuração S3/DigitalOcean Spaces no Laravel
[ ] Definir requisitos de segurança com time
[ ] Escrever FileUploadTest.php
[ ] Apresentar para revisão antes de implementação
```

---

## 4. Notificações em Tempo Real (WebSockets) ⚠️ ESCOPING

### ⚠️ Análise Complexidade

**Dependências:**
- Laravel Broadcasting
- Redis ou Pusher
- Frontend WebSocket client

**Riscos:**
- Connection drop → reconectar → dups
- Scaling horizontal → múltiplos servers
- Memory leaks em connections vazias

### 📝 Antes de Implementar

```
[ ] Definir provider (Redis vs Pusher vs Laravel alone)
[ ] Arquitetar sistema de reconexão
[ ] Criar testes de stress (1000+ connections)
[ ] Definir timeout/heartbeat
```

---

## 5. Busca Global (Elasticsearch/Meilisearch) 🔄 EM ANÁLISE

### 📋 Questões a Resolver

```
[ ] Meili vs Elasticsearch?
    - Meili: Simples, serverless
    - ES: Poderoso, complexo

[ ] Como sincronizar índice com DB?
    - Queue jobs
    - Webhooks
    - Real-time listeners

[ ] Quanto de delay é aceitável?
    - < 2s?
    - < 5s?

[ ] Performance de busca esperada?
    - 100 ms target?
    - 500 ms aceitável?
```

---

## 6. Gamificação (Badges) 🔄 PODE COMEÇAR

### 📋 Requisitos Básicos

```
[ ] Definir badges:
    - "Primeiro Post"
    - "Comentador Ativo"
    - "Alto Rating"
    
[ ] Como evitar gaming:
    - Cooldown entre ações?
    - Mínimo de reputação para contar?
    - Limite de badges por período?
```

### 🧪 Testes Necessários

```
[ ] Escrever testes para badge logic
[ ] Testes de prevenção de gaming
[ ] Testes de reputação calculation
```

---

## 📅 Ordem Sugerida de Implementação

| # | Funcionalidade | Status | Início |
|---|---|---|---|
| 1 | CRUD Obras | ✅ PRONTO | **SEMANA 1** |
| 2 | Edição Tópicos | ✅ PRONTO | **SEMANA 1** |
| 3 | Upload Imagens | ⚠️ PRONTO (com cuidado) | **SEMANA 2** |
| 4 | Notificações | 🔄 PREPARANDO | **SEMANA 3** |
| 5 | Busca | 🔄 EM ANÁLISE | **SEMANA 4** |
| 6 | Gamificação | 🔄 PODE COMEÇAR | **SEMANA 4** |

---

## 🚀 Para Começar Agora

### Próximo Passo: CRUD de Obras

**Tempo estimado: 4-6 horas**

```bash
# 1. Rodar testes atuais (devem falhar - RED)
cd backend
php artisan test tests/Feature/WorkCRUDTest.php

# 2. Implementar metodicamente (GREEN)
# 3. Refactor if needed (BLUE)
# 4. Merge quando todos testes passam (✅)
```

---

## ✅ Avaliação Antes de Cada Feature

**Checklist para começar qualquer feature:**

- [ ] Testes escritos e documentados no TESTING_STRATEGY.md
- [ ] Casos de teste (> 5 por feature)
- [ ] Critérios de aceitação definidos
- [ ] Possíveis problemas identificados
- [ ] Dependências externas listadas
- [ ] Revisão de design/arquitetura
- [ ] Aprovação do time

**Só depois:**

- [ ] Escrever testes (RED)
- [ ] Implementar (GREEN)
- [ ] Refactor (BLUE)
- [ ] Code review
- [ ] Merge
