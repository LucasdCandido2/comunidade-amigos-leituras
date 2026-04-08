# Fontes de Dados para Obras

## Fontes de Anime, Manga e Quadrinhos

### Sites de Referência e APIs

#### Anime & Manga
- **MyAnimeList** - https://myanimelist.net/
  - API oficial disponível
  - Maior banco de dados de anime/manga
  
- **AniList** - https://anilist.co/
  - GraphQL API pública
  - Dados de anime, manga, mangá
  
- **Kitsu** - https://kitsu.io/
  - API JSON:API
  - Opensource
  
- **Anime News Network** - https://www.animenewsnetwork.com/
  - API disponível
  
- **Jikan API (MyAnimeList não-oficial)** - https://jikan.moe/
  - API REST pública e gratuita

#### Quadrinhos (Comics/HQ)
- **ComicVine** - https://comicvine.gamespot.com/
  - API disponível
  
- **Marvel Comics API** - https://developer.marvel.com/
  - API oficial da Marvel
  
- **DC Comics API** - https://developer.dc.com/
  - API oficial da DC (em desenvolvimento)

#### Fontes Genéricas de Entretenimento
- **TheMovieDB (TMDB)** - https://www.themoviedb.org/
  - API para filmes, séries, animes
  - Dados de anime via TV
  
- **OMDB API** - https://www.omdbapi.com/
  - IMDb data

### Estrutura Sugerida para a Tabela de Fontes

```php
// migration
Schema::create('external_sources', function (Blueprint $table) {
    $table->id();
    $table->string('source_name'); // MyAnimeList, AniList, Marvel, etc.
    $table->string('source_url');  // URL base da API
    $table->string('api_endpoint')->nullable(); // Endpoint da API
    $table->string('api_key')->nullable(); // Chave de API se necessária
    $table->enum('media_type', ['anime', 'manga', 'comic', 'movie', 'series']);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Notas de Implementação

1. **MyAnimeList** é a fonte mais completa para anime/manga
2. **Jikan** é uma alternativa gratuita se não quiser pagar pela API oficial
3. Para comics, Marvel tem a melhor API documentada
4. Recomendo começar com uma fonte e expandir gradualmente

### Próximos Passos Sugeridos

1. Criar modelo e controller para ExternalSource
2. Adicionar método no WorkController para buscar dados externos
3. Implementar sincronização automática de metadados
4. Permitir que usuários sugiram obras via fonte externa

otimo, agora eu preciso consumir os conteudos, adicione tambem o do anilist e kitsune, tmdb, para poder popular a parte de obras dentro de gerenciar obras, se possivel, obtenha os links relacionados as obras para serem colocados nas referencias do obje de obras

mas antes de fazer isso, leia a documentacao atual existente sobre o projeto em si, ao final, rode todos os testes relacionados, migrations e seerders e tambem o usuario de teste tambem