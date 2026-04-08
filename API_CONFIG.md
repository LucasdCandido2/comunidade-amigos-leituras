# Configurações de API Keys - Comunidade Amigos Leituras

Este documento lista as APIs externas utilizadas no projeto e suas configurações necessárias.

## APIs Funcionando

### 1. Jikan (MyAnimeList) - ✅ FUNCIONANDO
- **Endpoint**: `https://api.jikan.moe/v4`
- **Tipo**: Público (sem API key necessária)
- **Conteúdo**: Anime e Manga
- **Status**: ✅ Já populado com ~99 animes e ~100 mangás

### 2. AniList - ✅ FUNCIONANDO
- **Endpoint**: `https://graphql.anilist.co`
- **Tipo**: Público (sem API key necessária)
- **Método**: GraphQL
- **Conteúdo**: Anime e Manga
- **Status**: ✅ Populando funciona

### 3. OMDB API - ✅ FUNCIONANDO
- **Endpoint**: `https://www.omdbapi.com/`
- **API Key**: Configurada no `.env` como `OMDB_API_KEY`
- **Conteúdo**: Movies e Series
- **Status**: ✅ Já populado com ~94 movies

---

## APIs com Problemas

### 4. Kitsu - ❌ NÃO FUNCIONA
- **Documentação**: https://kitsu.docs.apiary.io/
- **Problema**: A API retorna 404 "Route Not Found"
- **Possível causa**: A API mudou seu endpoint ou requer autenticação
- **Status**: ❌ Não populado

### 5. TMDB (TheMovieDB) - ❌ PRECISA API KEY
- **Documentação**: https://www.themoviedb.org/documentation/api
- **Endpoint**: `https://api.themoviedb.org/3`
- **Como obter**: 
  1. Criar conta em https://www.themoviedb.org/
  2. Ir em Settings > API
  3. Solicitar API Key (gratuito para desenvolvimento)
- **Variável de ambiente**: `TMDB_API_KEY`
- **Status**: ❌ Não populado (sem API key)

### 6. ComicVine - ❌ PRECISA API KEY
- **Documentação**: https://comicvine.gamespot.com/api/documentation
- **Endpoint**: `https://comicvine.gamespot.com/api/`
- **Como obter**:
  1. Criar conta em https://comicvine.gamespot.com/
  2. Ir em Settings > API
  3. Solicitar acesso API (requer aprovação)
- **Variável de ambiente**: `COMICVINE_API_KEY`
- **Status**: ❌ Não populado (sem API key)

---

## Como Configurar

### Via arquivo .env (backend)

```env
# OMDB (já configurado)
OMDB_API_KEY=sua_chave_aqui

# TMDB
TMDB_API_KEY=sua_chave_aqui

# ComicVine (requer aprovação)
COMICVINE_API_KEY=sua_chave_aqui
```

---

## Comandos para Popular Obras

```bash
# Populate via Jikan (anime)
php artisan works:populate --source=jikan --type=anime --limit=20

# Populate via Jikan (manga)
php artisan works:populate --source=jikan --type=manga --limit=20

# Populate via AniList (anime)
php artisan works:populate --source=anilist --type=anime --limit=20

# Populate via AniList (manga)
php artisan works:populate --source=anilist --type=manga --limit=20

# Populate via OMDB (movies)
php artisan works:populate --source=omdb --type=movie --limit=20

# Populate via TMDB (quando API key disponível)
php artisan works:populate --source=tmdb --type=movie --limit=20
```

---

## Resumo de Dados Populados

- **Anime (Jikan)**: ~99 obras
- **Manga (Jikan)**: ~100 obras  
- **Movies (OMDB)**: ~94 obras
- **Total**: ~293 obras

---

## Próximos Passos

1. [ ] Investigar Kitsu - a API parece ter mudado
2. [ ] Obter API key do TMDB (recomendado - movies e TV)
3. [ ] Popular movies via TMDB quando disponível
4. [ ] Popular comics via ComicVine (se necessário)