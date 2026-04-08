# Análise de Performance - Comunidade Amigos Leituras

## 🚨 Problema Identificado

**Tempo de resposta das APIs: ~4.4 segundos**

Este é um problema crítico - o tempo ideal para APIs é < 500ms.

---

## 📊 Análise por Camada

### 1. PHP-FPM (Causa principal)
```
pm.max_children = 5  ← MUITO BAIXO
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
```

**Problema**: Com apenas 5 processos PHP-FPM, qualquer pico de requisições causa fila de espera.

**Evidência nos logs**:
```
[pool www] server reached pm.max_children setting (5), consider raising it
```

### 2. Cache de API
O endpoint `/api/topics` já usa cache (300s), mas:
- Não tem cache para `/api/works/top`
- Não tem cache para `/api/user`
- Não há cache no frontend

### 3. Índices de Banco
✅ Já aplicados na migration `2026_04_05_130000_add_performance_indexes`

### 4. Eager Loading
✅ Já implementado corretamente com `Topic::with(['work', 'user'])`

---

## ⏱️ Tempos Medidos

| Endpoint | Tempo | Status |
|----------|-------|--------|
| GET /api/topics | 4.4s | 🔴 Crítico |
| GET /api/works/top | 4.4s | 🔴 Crítico |
| GET /api/user | 4.4s | 🔴 Crítico |

---

## ✅ Otimizações Imediatas (Prioridade Alta)

### 1. Aumentar PHP-FPM workers

```yaml
# docker-compose.yml - adicionar ao service app
php-fpm:
  environment:
    - PHP_FPM_PM=dynamic
    - PHP_FPM_PM_MAX_CHILDREN=20
    - PHP_FPM_PM_START_SERVERS=5
    - PHP_FPM_PM_MIN_SPARE_SERVERS=3
    - PHP_FPM_PM_MAX_SPARE_SERVERS=10
```

Ou criar arquivo de config dedicado.

### 2. Adicionar cache para Works

No `WorkController.php`:
```php
public function index(Request $request)
{
    $cacheKey = 'works:top:10';
    
    $works = Cache::remember($cacheKey, 600, function () {
        return Work::orderBy('bayesian_rating', 'desc')
            ->limit(10)
            ->get();
    });
    
    return response()->json($works);
}
```

### 3. Habilitar OPcache em produção

```ini
; php-prod.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 4. Adicionar cache no frontend

```javascript
// services/api.js - adicionar interceptors de cache
let cache = new Map();

api.interceptors.request.use((config) => {
    // GET requests com cache
    if (config.method === 'get' && cache.has(config.url)) {
        const cached = cache.get(config.url);
        if (Date.now() - cached.timestamp < 60000) { // 60s cache
            return Promise.resolve(cached.response);
        }
    }
    return config;
});
```

---

## 📋 Plano de Otimização Progressiva

### Fase 1: Configuração PHP-FPM (Imediato)
- [ ] Aumentar pm.max_children para 20
- [ ] Habilitar OPcache
- [ ] Monitorar logs

### Fase 2: Cache Backend (Curto prazo)
- [ ] Adicionar cache em /works/top
- [ ] Adicionar cache em /user
- [ ] Implementar cache de ranking

### Fase 3: Cache Frontend (Médio prazo)
- [ ] Implementar React Query ou SWR
- [ ] Adicionar Service Worker para offline
- [ ] Otimizar bundle size

### Fase 4: Infraestrutura (Longo prazo)
- [ ] Separar leitura/escrita (read replica)
- [ ] Configurar CDN para assets
- [ ] Implementar HTTP/2 ou HTTP/3

---

## 🎯 Meta

| Métrica | Atual | Meta |
|---------|-------|------|
| Tempo API | 4.4s | < 500ms |
| TTFB | ~4s | < 200ms |
| Time to Interactive | ? | < 3s |

---

## 🔧 Comandos de Diagnóstico

```bash
# Verificar processos PHP-FPM ativos
docker exec comunidade_app ps aux | grep php-fpm

# Verificar status do OPcache
docker exec comunidade_app php -r "print_r(opcache_get_status());"

# Verificar conexões PostgreSQL
docker exec comunidade_db psql -U user_admin -d comunidade_db -c "SELECT count(*) FROM pg_stat_activity WHERE datname = 'comunidade_db';"

# Verificar consultas lentas (se extensão disponível)
docker exec comunidade_db psql -U user_admin -d comunidade_db -c "SELECT query, calls, mean_time FROM pg_stat_statements ORDER BY mean_time DESC LIMIT 10;"
```
