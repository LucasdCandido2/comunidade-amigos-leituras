# Otimização de Banco de Dados - Comunidade Amigos Leituras

## 📋 Análise do Estado Atual

### Tabelas Existentes

| Tabela | Registros | Tamanho | Observações |
|--------|-----------|---------|-------------|
| users | ~1 | Pequeno | Sem índice em email |
| roles | 4 | Pequeno | - |
| permissions | 36 | Pequeno | - |
| user_role | ~1 | Pequeno | Pivot table |
| role_permission | ~30 | Pequeno | Pivot table |
| works | ~1-10 | Pequeno | Sem índices |
| topics | ~1-10 | Pequeno | Sem índices em work_id, user_id |
| interactions | ~10-50 | Pequeno | Sem índices |
| assets | ~0-10 | Pequeno | - |
| notifications | ~0-50 | Pequeno | Sem índices |
| votes | ~10-100 | Pequeno | Sem índices composite |
| gamification_tables | Variável | Pequeno | - |

### 🔴 Problemas Identificados

1. **Falta de Índices** - Nenhuma tabela tem índices além de PK
2. **Ausência de Foreign Keys** - Relaciones não enforced no DB
3. **N+1 Queries** - Backend não eager load relations corretamente
4. **Cache Incompleto** - Só tópicos tem cache
5. **Sem Query Optimization** - Não há where clauses otimizadas

---

## 🎯 Plano de Otimização - Fase 1: Índices

### 1.1 Índices de Busca

```sql
-- Tópicos
CREATE INDEX idx_topics_work_id ON topics(work_id);
CREATE INDEX idx_topics_user_id ON topics(user_id);
CREATE INDEX idx_topics_created_at ON topics(created_at DESC);
CREATE INDEX idx_topics_rating ON topics(rating DESC) WHERE rating IS NOT NULL;

-- Interações
CREATE INDEX idx_interactions_topic_id ON interactions(topic_id);
CREATE INDEX idx_interactions_user_id ON interactions(user_id);
CREATE INDEX idx_interactions_created_at ON interactions(created_at DESC);

-- Works
CREATE INDEX idx_works_type ON works(type);
CREATE INDEX idx_works_rating ON works(bayesian_rating DESC) WHERE bayesian_rating IS NOT NULL;
CREATE INDEX idx_works_title_trgm ON works(title) USING gin(to_tsvector('portuguese', title));

-- Votes (CRÍTICO para performance)
CREATE INDEX idx_votes_votable ON votes(votable_type, votable_id);
CREATE INDEX idx_votes_user_votable ON votes(user_id, votable_type, votable_id) UNIQUE;

-- Notificações
CREATE INDEX idx_notifications_user_id_read ON notifications(user_id, is_read) WHERE is_read = false;

-- Assets
CREATE INDEX idx_assets_topic_id ON assets(topic_id);
CREATE INDEX idx_assets_user_id ON assets(user_id);
```

### 1.2 Foreign Keys com índices

```sql
-- Adicionar Foreign Keys com índices automáticos
ALTER TABLE topics ADD CONSTRAINT fk_topics_work FOREIGN KEY (work_id) REFERENCES works(id) ON DELETE CASCADE;
ALTER TABLE topics ADD CONSTRAINT fk_topics_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE interactions ADD CONSTRAINT fk_interactions_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE;
ALTER TABLE interactions ADD CONSTRAINT fk_interactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE votes ADD CONSTRAINT fk_votes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
```

---

## 🎯 Plano de Otimização - Fase 2: Queries

### 2.1 Eager Loading no Laravel

```php
// TopicController - já tem, mas precisa expandir
$topics = Topic::with(['work', 'user'])
    ->withCount(['interactions', 'votes'])
    ->paginate(10);

// Works - adicionar
$works = Work::with(['user'])
    ->withAvgRating()
    ->paginate(20);
```

### 2.2 Queries otimizadas para列表

```php
// Evitar N+1 - sempre eager load relations
$topics = Topic::with(['work:id,title', 'user:id,name', 'votes'])
    ->get();

// Usar select específico quando não precisa de tudo
$topics = Topic::select('id', 'title', 'content', 'user_id', 'work_id', 'rating', 'created_at')
    ->with(['work:id,title', 'user:id,name'])
    ->paginate(10);
```

---

## 🎯 Plano de Otimização - Fase 3: Cache

### 3.1 Cache Strategy

```php
// Cache por página - não por ID
Cache::remember("works:page:{$page}:limit:{$limit}", 300, function() use ($page, $limit) {
    return Work::with(['user:id,name'])->paginate($limit);
});

// Cache de contadores
Cache::remember('works:count', 3600, function() {
    return Work::count();
});

// Cache de rankings
Cache::remember('works:top:10', 600, function() {
    return Work::orderByDesc('bayesian_rating')->limit(10)->get();
});
```

---

## 🎯 Plano de Otimização - Fase 4: Estrutura

### 4.1 Conexões Separadas (Se Necesário)

- Leituras: Replica (se tiver)
- Escritas: Primary

### 4.2 Particionamento

Para grandes volumes:
- `votes` - particionar por month(created_at)
- `notifications` - particionar por user_id ranges

---

## 📊 Comandos de Análise

### Verificar queries lentas
```sql
-- PostgreSQL
SELECT query, calls, mean_time, total_time 
FROM pg_stat_statements 
ORDER BY mean_time DESC 
LIMIT 20;
```

### Verificar índices existentes
```sql
SELECT 
    t.relname AS table_name,
    i.relname AS index_name,
    idx.*
FROM pg_class t
JOIN pg_index idx ON t.oid = idx.indrelid
JOIN pg_class i ON i.oid = idx.indexrelid
WHERE t.relname NOT LIKE 'pg_%'
ORDER BY t.relname, i.relname;
```

### Verificar tamanho de tabelas
```sql
SELECT 
    relname AS table_name,
    pg_size_pretty(pg_total_relation_size(relid)) AS total_size,
    pg_size_pretty(pg_relation_size(relid)) AS table_size
FROM pg_catalog.pg_statio_user_tables
ORDER BY pg_total_relation_size(relid) DESC;
```

---

## ✅ Checklist de Implementação

### Prioridade Alta (Fase 1)
- [ ] Adicionar índices em topics (work_id, user_id, created_at)
- [ ] Adicionar índices em interactions (topic_id, user_id)
- [ ] Adicionar índices em votes (votable_type, votable_id, user_id)
- [ ] Adicionar índices em works (type, bayesian_rating)
- [ ] Adicionar índice em notifications (user_id, is_read)

### Prioridade Média (Fase 2)
- [ ] Revisar todas as queries do backend para eager loading
- [ ] Adicionar cache em listagens de works
- [ ] Adicionar cache em interações count
- [ ] Otimizar queries de gamificação

### Prioridade Baixa (Fase 3)
- [ ] Implementar query com select específico
- [ ] Revisar paginação
- [ ] Adicionar monitoring de queries lentas

---

## ⚡ Resultado Esperado

| Query | Antes | Depois | Melhoria |
|-------|-------|--------|-----------|
| Lista tópicos | ~500ms | ~50ms | 90% |
| Lista works | ~300ms | ~30ms | 90% |
| Detalhe tópico | ~200ms | ~20ms | 90% |
| Rankings | ~400ms | ~50ms | 87% |

**Meta**: Tempo total de carregamento < 2s para qualquer página