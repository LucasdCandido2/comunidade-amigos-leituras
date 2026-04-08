# Diretrizes de Desenvolvimento

## Checklist Obrigatório Antes de Finalizar Qualquer Trabalho

### 1. Testes
- [ ] Rodar testes do backend: `php artisan test` ou `composer test`
- [ ] Rodar testes do frontend: `npm test` (se disponível)
- [ ] Verificar se todos os testes passam

### 2. Verificação de Código
- [ ] Executar lint/typecheck do PHP: `composer run-script check-syntax` ou `php -l`
- [ ] Executar lint do JS/React: `npm run lint` (se disponível)
- [ ] Verificar syntax errors

### 3. Banco de Dados
- [ ] Verificar se há migrations pendentes: `php artisan migrate:status`
- [ ] Rodar migrations: `php artisan migrate`
- [ ] Rodar seeders se necessário: `php artisan db:seed`
- [ ] Verificar se as tabelas foram criadas corretamente

### 4. Logs
- [ ] Verificar logs do Laravel: `storage/logs/laravel.log`
- [ ] Verificar logs do container Docker (frontend/backend)
- [ ] Checar por erros críticos nos logs

### 5. Containers Docker
- [ ] Verificar status dos containers: `docker-compose ps`
- [ ] Checar logs do container PHP/Laravel
- [ ] Checar logs do container frontend (npm/node)
- [ ] Verificar se os serviços estão rodando sem erros

---

## Padrões de Código

### PHP/Laravel
- Usar PSR-12 para formatacao
- Nomes de classes em PascalCase
- Métodos e variáveis em camelCase
- Usar type hints onde possível
- Documentar funções complexas

### React/JavaScript
- Usar Hooks do React (useState, useEffect, etc)
- Componentes funcionais
- Nomes de componentes em PascalCase
- Nomes de funções/variáveis em camelCase

### CSS
- Usar variáveis CSS do design system
- Seguir convenções BEM para classes
- Manter consistência com o arquivo components.css

---

## Fluxo de Trabalho

1. **Antes de começar**: Entender o problema, verificar código existente
2. **Durante o desenvolvimento**: Fazer pequenas alterações testáveis
3. **Antes de finalizar**: Executar checklist obrigatório
4. **Finalizar**: Resumir alterações feitas para o usuário

---

## Configurações de Ambiente

- Backend: Laravel 10 + PHP 8.x + PostgreSQL
- Frontend: React 18 + Vite
- Docker Compose para orchestração
- Bearer Token para autenticação API