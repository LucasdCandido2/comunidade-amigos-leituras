# 🧪 Estratégia de Testes - Funcionalidades Avançadas

## Metodologia
**Test-Driven Development (TDD)** - Testes → Problemas → Implementação

---

## 1. CRUD Completo de Obras (Frontend)

### 📋 Análise de Possibilidades de Problema

#### Backend Issues
- [ ] Validação inadequada de dados na edição
- [ ] Permissões: Quem pode editar obra? (criador, admin, dono?)
- [ ] Concorrência: Multiple usuarios editando simultaneamente
- [ ] Soft delete vs hard delete - impacto em tópicos relacionados
- [ ] Atualização de ranking quando obra é editada

#### Frontend Issues
- [ ] Estados de loading/erro não tratados
- [ ] Validação de formulário (título vazio, descrição muito longa)
- [ ] Conflito de estado entre edição e criação
- [ ] URL inválida no campo canonical_url
- [ ] Tipo de obra alterado após criação - validação

#### API Issues
- [ ] Rota PUT/PATCH não existe
- [ ] Rota DELETE não existe
- [ ] Sem tratamento de 404 (obra não encontrada)
- [ ] Sem tratamento de 403 (não autorizado)

---

### ✅ Casos de Teste

#### Testes Unitários (Backend)

```
✓ PUT /api/works/{id} - Editar obra com dados válidos
✓ PUT /api/works/{id} - Retorna 404 se obra não existe
✓ PUT /api/works/{id} - Retorna 403 se usuário não é criador
✓ PUT /api/works/{id} - Validação: título obrigatório
✓ PUT /api/works/{id} - Validação: tipo em enum válido
✓ PUT /api/works/{id} - Validação: URL canônica válida
✓ DELETE /api/works/{id} - Soft delete (work existe mas deleted_at preenchido)
✓ DELETE /api/works/{id} - Retorna 404 se obra não existe
✓ DELETE /api/works/{id} - Retorna 403 se usuário não é criador
✓ GET /api/works/{id} - Não retorna obras deletadas
✓ Edição de obra recalcula ranking dos tópicos
```

#### Testes de Integração (Frontend)

```
✓ CreateTopic → Editar obra existente
✓ WorksRanking → Clicar em obra → Ir para edição
✓ Edição: Preencher formulário corretamente
✓ Edição: Validação de campos (título vazio)
✓ Edição: Envio com sucesso atualiza lista
✓ Edição: Erro 403 mostra mensagem apropriada
✓ Deleção: Confirmação antes de deletar
✓ Deleção: Atualiza lista após sucesso
✓ Deleção: Trata erro 403 apropriadamente
```

#### Testes E2E

```
✓ Login → Criar obra → Editar → Deletar
✓ Editar obra com 50+ caracteres em descrição
✓ Editar obra com URL canônica complexa
✓ Deletar obra e verificar que tópicos ainda existem
```

---

### 🎯 Critérios de Aceitação

**AC-1: Editar Obra**
- Dado: Usuário autenticado visualizando uma obra
- Quando: Clicar em "Editar"
- Então: Formulário pré-preenchido com dados da obra
- E: Botão "Salvar" envia PUT com dados validados
- E: Sucesso mostra mensagem "Obra atualizada"
- E: Erro mostra mensagem apropriada

**AC-2: Deletar Obra**
- Dado: Usuário autenticado visualizando uma obra
- Quando: Clicar em "Deletar"
- Então: Confirmação é pedida
- E: DELETE remove obra do banco
- E: Obra desaparece da lista em tempo real
- E: Tópicos relacionados continuam visíveis

**AC-3: Validações**
- Título: 1-255 caracteres, obrigatório
- Tipo: Deve estar em enum (book, manga, anime, comic)
- URL: Formato válido ou vazio
- Descrição: Máx 1000 caracteres

---

## 2. Upload de Imagens/Arquivos para Tópicos

### 📋 Análise de Possibilidades de Problema

#### Críticos
- [ ] File size limit não configurado → RAM overflow
- [ ] MIME type validation absent → Malware upload
- [ ] Sem antivírus/scan de arquivo
- [ ] Sem limite de uploads por usuário
- [ ] Path traversal vulnerability

#### Backend
- [ ] DigitalOcean Spaces credentials não configurados
- [ ] Signed URLs expiram → Links quebrados
- [ ] Sem compressão de imagem → Bandwidth alto
- [ ] Erro em upload parcial → Arquivo corrompido
- [ ] Sem rollback em erro

#### Frontend
- [ ] Sem preview da imagem antes de upload
- [ ] Sem barra de progresso
- [ ] Sem tratamento de timeout
- [ ] Múltiplos uploads simultâneos

---

### ✅ Casos de Teste

```
✓ Upload imagem válida (PNG/JPG) < 5MB
✓ Rejeita arquivo > limite (ex: 100MB)
✓ Rejeita tipo inválido (exe, zip)
✓ Rejeita upload sem autenticação
✓ Comprime imagem antes de upload
✓ Gera URL signed com expiração 30 dias
✓ URL expires após 30 dias
✓ Upload display erro se falha
✓ Preview mostra imagem antes de submit
✓ Barra de progresso atualiza
```

---

## 3. Edição/Deleção de Tópicos (Frontend)

### 📋 Análise de Possibilidades de Problema

#### Lógica
- [ ] Quem pode editar? (criador, admin?)
- [ ] Histórico de edições?
- [ ] Comentários ficam se tópico é deletado?
- [ ] Atualizar timestamp de modificação

#### Frontend
- [ ] Modal de confirmação antes de deletar
- [ ] Sem pesimismo - precisa confirmar
- [ ] Conflito de state ao editar

---

### ✅ Casos de Teste

```
✓ PUT /api/topics/{id} - Editar com dados válidos
✓ PUT /api/topics/{id} - Retorna 403 se não é criador
✓ DELETE /api/topics/{id} - Soft delete
✓ Comentários permanecem após deleção de tópico
✓ Frontend: Modal de confirmação mostra
✓ Frontend: Tópico desaparece após deleção
```

---

## 4. Notificações em Tempo Real (WebSockets)

### 📋 Análise de Possibilidades de Problema

#### Críticos
- [ ] Connection dropout → Reconnect?
- [ ] Memory leak em connections
- [ ] Sem heartbeat → Detecção de dead connections
- [ ] Scaling: Como funciona com múltiplos servidores?
- [ ] Message queue overflow

#### Backend
- [ ] Redis/Queue não configurado
- [ ] Broadcasting auth não validado
- [ ] Rate limits em eventos

---

### ✅ Casos de Teste

```
✓ WebSocket conecta quando usuário entra
✓ Novo comentário notifica usuários em tempo real
✓ Desconexão reconecta automaticamente
✓ Reconexão não duplica notificações
✓ Permissões: Não notifica users não autorizados
```

---

## 5. Busca Global (Elasticsearch/Meilisearch)

### 📋 Análise de Possibilidades de Problema

#### Sincronização
- [ ] Índice desincronizado com DB
- [ ] Remoção de tópicos não remove do índice
- [ ] Edição de tópico não atualiza índice

#### Performance
- [ ] Timeout em busca lenta
- [ ] Limite de resultados não configurado

---

### ✅ Casos de Teste

```
✓ Busca retorna tópicos contendo keyword
✓ Busca retorna obras contendo keyword
✓ Busca exclui tópicos deletados
✓ Busca com 0 resultados mostra mensagem
✓ Busca com XSS não quebra
✓ Novo tópico aparece em busca em < 2s
```

---

## 6. Gamificação (Badges, Reputação)

### 📋 Análise de Possibilidades de Problema

#### Lógica
- [ ] Game breaking: Como evitar spam de comentários para badges?
- [ ] Timestamps: Badges só contam após X dias?
- [ ] Sybil attack: Múltiplas contas

#### Backend
- [ ] Sem limite de badges por usuário
- [ ] Sem validação de critério

---

### ✅ Casos de Teste

```
✓ Usuário ganha badge após criar 1º tópico
✓ Badge aparece no perfil do usuário
✓ Não ganha 2x mesma badge
✓ Reputação aumenta com comentários rated bem
✓ Reputação não aumenta com downvotes
```

---

## 🗂️ Estrutura de Pastas - Testes

```
/tests
├── Feature/
│   ├── WorkCRUDTest.php
│   ├── TopicCRUDTest.php
│   ├── FileUploadTest.php
│   └── WebSocketTest.php
└── Unit/
    ├── WorkValidationTest.php
    ├── RankingCalculationTest.php
    └── BadgeLogicTest.php

/frontend/__tests__
├── components/
│   ├── WorkEditor.test.jsx
│   ├── FileUpload.test.jsx
│   └── NotificationCenter.test.jsx
└── services/
    └── workService.test.js
```

---

## 📊 Priorização

1. **P1 (Crítico)**: CRUD de Obras, Edição de Tópicos
2. **P2 (Alto)**: Upload de Imagens, Notificações
3. **P3 (Médio)**: Busca, Gamificação

---

## ✅ Checklist de Implementação

- [ ] Testes escritos e rodando (RED)
- [ ] Código implementado (GREEN)
- [ ] Refactoring (REFACTOR)
- [ ] Testes passando com cobertura > 80%
- [ ] Code review
- [ ] Merge para main
