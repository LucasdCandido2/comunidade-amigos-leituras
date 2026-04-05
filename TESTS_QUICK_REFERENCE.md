# 🧪 Quick Reference - Rodando Testes

## Como Rodar Testes Por Feature

### 1️⃣ CRUD de Obras

**Backend (19 testes - Estimated 2 min)**
```bash
cd backend
php artisan test tests/Feature/WorkCRUDTest.php --verbose

# Ou com filter
php artisan test tests/Feature/WorkCRUDTest.php --filter="work_with_valid_data"
```

**Frontend (14+ testes - Estimated 1 min)**
```bash
cd frontend
npm test -- workService.test.js
npm test -- workService.test.js --coverage
```

---

### 2️⃣ CRUD de Tópicos

**Backend (15+ testes - Estimated 2 min)**
```bash
cd backend
php artisan test tests/Feature/TopicCRUDTest.php --verbose
```

**Frontend (quando pronto)**
```bash
cd frontend
npm test -- topicService.test.js --coverage
```

---

### 3️⃣ Upload de Imagens

**Backend (quando testes criados)**
```bash
php artisan test tests/Feature/FileUploadTest.php
```

**Frontend (quando testes criados)**
```bash
npm test -- components/FileUpload.test.jsx
```

---

## Rodar TODOS os Testes

### Backend - Todos os testes
```bash
cd backend
php artisan test

# Ou com coverage
php artisan test --coverage
```

### Frontend - Todos os testes
```bash
cd frontend
npm test

# Ou com coverage
npm test -- --coverage
```

---

## Testes Específicos de Uma Funcionalidade

### Exemplo: Testar apenas "create_work_with_valid_data"
```bash
cd backend
php artisan test tests/Feature/WorkCRUDTest.php --filter="create_work_with_valid_data"
```

### Exemplo: Testar apenas validações
```bash
cd backend
php artisan test tests/Feature/WorkCRUDTest.php --filter="validates"
```

---

## CI/CD - Verificar Antes de Commit

### Pre-commit Hook
```bash
# Backend
cd backend && php artisan test && cd ..

# Frontend
cd frontend && npm test -- --coverage && cd ..

# Se ambos passam, fazer commit
```

---

## Interpretando Resultados

### ✅ PASSOU
```
✓ test_create_work_with_valid_data
```

### ❌ FALHOU
```
✗ test_create_work_with_valid_data
  Expected: 201
  Got: 500
```

### ⏭️ SKIPPED
```
⊙ test_update_work_validates_ownership
```

---

## Coverage Esperada Por Feature

### CRUD Obras: 80%+
```bash
php artisan test tests/Feature/WorkCRUDTest.php --coverage

# Esperado output:
# Code Coverage: 82.5%
```

### CRUD Tópicos: 80%+
```bash
php artisan test tests/Feature/TopicCRUDTest.php --coverage
```

### Frontend: 75%+
```bash
npm test -- --coverage workService.test.js
```

---

## Troubleshooting

### Teste falha por timeout
```bash
# Aumentar timeout
php artisan test tests/Feature/WorkCRUDTest.php --env=testing timeout=300
```

### SQLite database locked
```bash
# Deletar DB de teste
rm backend/database/testing.sqlite
php artisan migrate --env=testing
php artisan test
```

### Node/npm cache issues
```bash
cd frontend
npm cache clean --force
npm install
npm test
```

---

## Verificação Final Antes de Merge

```bash
# 1. Rodar todos os testes atuais
cd backend && php artisan test && cd ..

# 2. Rodar especificamente os da feature
php artisan test tests/Feature/WorkCRUDTest.php

# 3. Check coverage
php artisan test tests/Feature/WorkCRUDTest.php --coverage

# 4. Frontend tests
cd frontend && npm test -- --coverage && cd ..

# 5. Type checking (se usar TypeScript)
npm run type-check

# 6. Lint
npm run lint
```

✅ Se tudo passar → Pode fazer merge!
