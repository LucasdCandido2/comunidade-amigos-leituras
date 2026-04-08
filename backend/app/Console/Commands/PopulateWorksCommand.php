<?php

namespace App\Console\Commands;

use App\Models\ExternalSource;
use App\Models\Work;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PopulateWorksCommand extends Command
{
    protected $signature = 'works:populate {--source=jikan : Fonte externa (jikan, anilist, kitsu, tmdb)} {--type=anime : Tipo de mídia (anime, manga, movie)} {--limit=20 : Número de resultados}';

    protected $description = 'Popula obras a partir de APIs externas (Jikan, AniList, Kitsu, TMDB)';

    private const JIKAN_BASE = 'https://api.jikan.moe/v4';
    private const ANILIST_ENDPOINT = 'https://graphql.anilist.co';
    private const KITSU_BASE = 'https://kitsu.io/api/edge';
    private const TMDB_BASE = 'https://api.themoviedb.org/3';
    private const OMDB_BASE = 'https://www.omdbapi.com/';

    // Mapeamento de gêneros para classificações internas
    private const GENRE_MAP = [
        'Action' => 'action', 'Adventure' => 'adventure', 'Comedy' => 'comedy',
        'Drama' => 'drama', 'Fantasy' => 'fantasy', 'Horror' => 'horror',
        'Mecha' => 'mecha', 'Mystery' => 'mystery', 'Psychological' => 'psychological',
        'Romance' => 'romance', 'Sci-Fi' => 'sci-fi', 'Seinen' => 'seinen',
        'Shoujo' => 'shoujo', 'Shounen' => 'shounen', 'Slice of Life' => 'slice-of-life',
        'Sports' => 'sports', 'Supernatural' => 'supernatural', 'Thriller' => 'thriller',
        'Isekai' => 'isekai', 'Military' => 'military', 'Music' => 'music',
        'School' => 'school', 'Demons' => 'demons', 'Vampire' => 'vampire',
        'Magic' => 'magic', 'Samurai' => 'samurai', 'Historical' => 'historical',
        'Police' => 'police', 'Super Power' => 'super-power', 'Game' => 'game',
    ];

    public function handle(): int
    {
        $source = $this->option('source');
        $type = $this->option('type');
        $limit = (int) $this->option('limit');

        $this->info("Populando obras via {$source} ({$type}) - máximo {$limit} itens...");

        $populatedCount = 0;

        try {
            $searchTerms = $this->getSearchTerms($type);

            foreach ($searchTerms as $term) {
                $results = $this->search($source, $term, $type, $limit);
                
                foreach ($results as $item) {
                    if ($this->createWork($item, $source)) {
                        $populatedCount++;
                    }
                }

                // Rate limiting between requests
                sleep(2);
            }

            $this->info("✅ Concluído! {$populatedCount} obras populadas.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            Log::error('PopulateWorksCommand error', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    private function getSearchTerms(string $type): array
    {
        return match($type) {
            'anime' => [
                // Top Animes Clássicos
                'Naruto', 'One Piece', 'Attack on Titan', 'Death Note', 'Fullmetal Alchemist', 
                'Dragon Ball Z', 'Bleach', 'My Hero Academia', 'Demon Slayer', 'Jujutsu Kaisen',
                'Hunter x Hunter', 'JoJo Bizarre Adventure', 'Sword Art Online', 'Tokyo Revengers',
                'Black Clover', 'Boruto', 'Fairytail', ' Fairy Tail', 'Noragami', 'Mob Psycho 100',
                'Violet Evergarden', 'Re:Zero', 'Sword Art Online', 'Steins;Gate', 'Code Geass',
                'Cowboy Bebop', 'Neon Genesis Evangelion', 'Haikyuu', 'Yuri on Ice', 'Kuroko Basketball',
                'Haikyuu', 'Kuroko no Basuke', 'Initial D', 'Cardcaptor Sakura', 'Sailor Moon',
                'Detective Conan', 'Case Closed', 'Gintama', 'Soul Eater', 'Assassination Classroom',
                'Food Wars', 'Dr Stone', 'Promised Neverland', 'The God of High School',
                'Fire Force', 'Mob', 'One Punch Man', 'Overlord', 'KonoSuba', 'Log Horizon',
                'No Game No Life', 'Hyouka', 'Temple', 'Gochuumon wa Usagi Desu ka',
                'Natsume Book of Friends', 'Clannad', 'Angel Beats', 'Charlotte', 'Key',
                'Clannad After Story', 'Plastic Memories', 'Erased', 'Paranoia Agent',
                // Animes Populares Recentes
                'Chainsaw Man', 'Spy x Family', 'Mobuseka', 'Mushoku Tensei', 'Kagurabachi',
                'Sakamoto Days', 'Blue Lock', 'Undead Girl Machiavelli', 'Rururin',
                // Isekai Populares
                'That Time I Got Reincarnated as a Slime', 'Reincarnated as a Sword',
                'The Rising of the Shield Hero', 'KonoSuba', 'Overlord', 'Log Horizon',
                'Sword Art Online Alternative GGO', 'Isekai Slime', 'Tensei Slime',
                // Shounen Populares
                'Blue Lock', 'Kengan Ashura', 'God of High School', 'Hajime no Ippo',
                'Mediaboy', 'Sakamoto Days', 'Mysterious Disappearance',
                // Animes de Ação
                'Baki', 'Grappler Baki', 'Kengan Ashura', 'Baki Hanma', 'Shakunetsu',
                // Animes de Comédia
                'Grand Blue', 'Kakushigoto', 'Gintama', 'Spy x Family', 'Osakoi',
            ],
            'manga' => [
                // Top Mangás Clássicos
                'One Piece', 'Naruto', 'Bleach', 'Attack on Titan', 'Fullmetal Alchemist', 
                'Death Note', 'Berserk', 'Tokyo Ghoul', 'Hunter x Hunter', 'JoJo Bizarre Adventure',
                'Berserk', 'Vinland Saga', 'Kingdom', 'One Punch Man', 'Mob Psycho 100',
                'Oshi no Ko', 'Chainsaw Man', 'Jujutsu Kaisen', 'Demon Slayer', 'Spy x Family',
                // Shoujo Populares
                'Kimi ni Todoke', 'Fruits Basket', 'Natsume Book of Friends', 'Ouran Host Club',
                'Maid Sama', 'Special A', 'Toradora', 'Clannad', 'Angel Beats',
                // Shoujo Moderno
                'Love Live', 'Blue Period', 'Skip Beat', 'Kakushigoto', 'Act Age',
                // Seinen Populares
                'Vagabond', 'Monster', '20th Century Boys', 'Pluto', 'Billy Bat',
                'Goodnight Punpun', 'Grand Blue', 'Kingdom', 'Vinland Saga', 'Blame',
                // Mangás de Horror
                'Uzumaki', 'Junji Ito Collection', 'Tomie', 'Gyo', 'Uzumaki',
                // Mangás de Comédia
                'Grand Blue', 'Kakushigoto', 'Spy x Family', 'Dr Stone',
                // Mangás de Romance
                'Kimi ni Todoke', 'Kakegurui', 'Sayonara Zetsubou Sensei',
                // Mangás de Suspense
                'Monster', 'Paranoia Agent', 'Death Note', 'Kuroshitsuji',
                // Mangás de Sci-Fi
                'Blame', 'Ghost in the Shell', ' Akira', 'Planet of the Apes',
            ],
            'movie' => [
                // Blockbusters
                'Inception', 'Avatar', 'Titanic', 'The Matrix', 'Interstellar', 
                'The Dark Knight', 'Avengers Endgame', 'Spider-Man No Way Home', 
                'Avatar The Way of Water', 'Top Gun Maverick', 'Barbie', 'Oppenheimer',
                'Everything Everywhere All at Once', 'Parasite', 'Joker', 'The Batman',
                // Filmes de Ação
                'John Wick', 'Mission Impossible', 'Fast X', 'Transformers',
                'The Equalizer', 'Nobody', 'Extraction', 'Red Notice',
                // Filmes de Animação
                'Spider-Man Into the Spider-Verse', 'The Incredibles', 'Toy Story',
                'Finding Nemo', 'Up', 'WALL-E', 'Coco', 'Soul', 'Turning Red',
                'Elemental', 'Wish', 'Despicable Me', 'Minions', 'Shrek', 'Kung Fu Panda',
                'How to Train Your Dragon', 'Inside Out', 'Onward', 'Luca',
                'Encanto', 'Moana', 'Zootopia', 'Big Hero 6', 'Raya',
                // Filmes de Comédia
                'Superbad', 'Step Brothers', 'Night at the Museum', 'Groundhog Day',
                'Game Night', 'Jumanji', 'The Hangover', 'Booksmart',
                // Filmes de Horror
                'It', 'A Quiet Place', 'Get Out', 'Us', 'Scream',
                'Annabelle', 'The Conjuring', 'The Ring', 'Paranormal Activity',
                // Filmes de Romance
                'The Notebook', 'La La Land', 'Crazy Rich Asians', 'PRIDE & PREJUDICE',
                'Bridgerton', 'Moulin Rouge', 'Titanic', 'Romeo + Juliet',
                // Filmes de Fantasia
                'Harry Potter', 'Lord of the Rings', 'The Hobbit', 'Fantastic Beasts',
                'Encanto', 'Frozen', 'Maleficent', 'Cinderella', 'Beauty and the Beast',
                // Filmes de Sci-Fi
                'Dune', 'Star Wars', 'Avatar', 'Back to the Future', 'Terminator',
                'Alien', 'Blade Runner', 'The Matrix', 'Minority Report',
                // Filmes de Drama
                'Forrest Gump', 'The Shawshank Redemption', 'Pulp Fiction',
                'Fight Club', 'The Social Network', 'The Godfather', 'Goodfellas',
            ],
            'novel' => [
                // Light Novels Populares
                'Sword Art Online', 'Re:Zero', 'Overlord', 'KonoSuba', 'Log Horizon',
                'Mushoku Tensei', 'That Time I Got Reincarnated as a Slime',
                'The Rising of the Shield Hero', 'No Game No Life', 'Hyouka',
                'Roukin', 'Kabaneri of the Iron Fortress', 'Monogatari Series',
                // Romances
                'Fruits Basket', 'Kimi ni Todoke', 'Maid Sama', 'Toradora',
                'Clannad', 'Angel Beats', 'Natsume Yuujinchou',
            ],
            default => ['Naruto', 'One Piece', 'Attack on Titan', 'Dragon Ball', 'Demon Slayer'],
        };
    }

    private function search(string $source, string $query, string $type, int $limit): array
    {
        return match($source) {
            'anilist' => $this->searchAniList($query, $type, $limit),
            'kitsu' => $this->searchKitsu($query, $type, $limit),
            'tmdb' => $this->searchTMDB($query, $type, $limit),
            'omdb' => $this->searchOMDB($query, $type, $limit),
            default => $this->searchJikan($query, $type, $limit),
        };
    }

    private function searchJikan(string $query, string $type, int $limit): array
    {
        // Map types to Jikan endpoints
        $typeMap = [
            'anime' => 'anime',
            'manga' => 'manga',
            'movie' => 'anime', // fallback
        ];
        
        $jikanType = $typeMap[$type] ?? 'anime';
        
        $this->info("Searching Jikan: {$query} (type: {$jikanType})");
        
        // Use direct endpoint: /{type}?q=...
        $endpoint = self::JIKAN_BASE . "/{$jikanType}?q=" . urlencode($query) . "&limit={$limit}";
        
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'ComunidadeAmigosLeituras/1.0',
                'Accept' => 'application/json',
            ])
            ->get($endpoint);
        
        if (!$response->successful()) {
            $this->warn("Jikan API error para '{$query}': status " . $response->status() . " - " . substr($response->body(), 0, 200));
            Log::warning('Jikan API error', [
                'query' => $query,
                'status' => $response->status(),
                'response' => substr($response->body(), 0, 500),
            ]);
            return [];
        }

        return array_map(fn($item) => $this->formatJikanItem($item, $type), $response->json('data', []));
    }

    private function searchAniList(string $query, string $type, int $limit): array
    {
        $mediaType = strtoupper($type);

        $response = Http::timeout(30)->post(self::ANILIST_ENDPOINT, [
            'query' => '
                query ($search: String, $type: MediaType) {
                    Page(perPage: ' . $limit . ') {
                        media(search: $search, type: $type) {
                            id
                            title { romaji english native }
                            description
                            coverImage { large medium }
                            siteUrl
                            type
                            format
                            episodes
                            chapters
                            status
                            averageScore
                        }
                    }
                }
            ',
            'variables' => ['search' => $query, 'type' => $mediaType],
        ]);

        if (!$response->successful()) {
            $this->warn("AniList API error para '{$query}': " . $response->status());
            return [];
        }

        return array_map(fn($item) => $this->formatAniListItem($item, $type), $response->json('data.Page.media', []));
    }

    private function searchKitsu(string $query, string $type, int $limit): array
    {
        $mediaType = $type === 'movie' ? 'anime' : $type;

        // Kitsu requires JSON:API headers according to documentation
        $response = Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])
            ->get(self::KITSU_BASE . '/'. $mediaType .'s', [
                'filter[text]' => $query,
                'page[limit]' => $limit,
            ]);

        if (!$response->successful()) {
            $this->warn("Kitsu API error para '{$query}': " . $response->status() . " - " . substr($response->body(), 0, 200));
            return [];
        }

        return array_map(fn($item) => $this->formatKitsuItem($item, $type), $response->json('data', []));
    }

    private function searchTMDB(string $query, string $type, int $limit): array
    {
        $mediaType = $type === 'anime' ? 'tv' : ($type === 'movie' ? 'movie' : 'tv');
        
        // Note: TMDB requires API key - using demo key for testing
        $apiKey = config('services.tmdb.api_key', '');
        
        if (!$apiKey) {
            $this->warn("TMDB API key não configurada. Pulando TMDB.");
            return [];
        }

        $response = Http::timeout(30)->get(self::TMDB_BASE . '/search/' . $mediaType, [
            'query' => $query,
            'api_key' => $apiKey,
        ]);

        if (!$response->successful()) {
            $this->warn("TMDB API error para '{$query}': " . $response->status());
            return [];
        }

        return array_map(fn($item) => $this->formatTMDBItem($item, $type), $response->json('results', []));
    }

    private function searchOMDB(string $query, string $type, int $limit): array
    {
        $apiKey = config('services.omdb.api_key', '');
        
        if (!$apiKey) {
            $this->warn("OMDB API key não configurada. Pulando OMDB.");
            return [];
        }

        $searchType = $type === 'movie' ? 'movie' : 'series';
        
        $response = Http::timeout(30)->get(self::OMDB_BASE, [
            's' => $query,
            'type' => $searchType,
            'apikey' => $apiKey,
        ]);

        if (!$response->successful()) {
            $this->warn("OMDB API error para '{$query}': " . $response->status());
            return [];
        }

        $data = $response->json();
        if (empty($data['Search'])) {
            return [];
        }

        return array_map(fn($item) => $this->formatOMDBItem($item, $type), $data['Search']);
    }

    private function formatOMDBItem(array $item, string $type): array
    {
        $source = ExternalSource::where('name', 'like', '%OMDB%')
            ->where('media_type', $type)
            ->first();

        return [
            'source' => $source?->id,
            'external_id' => $item['imdbID'] ?? '',
            'title' => $item['Title'] ?? '',
            'title_english' => null,
            'description' => '',
            'type' => $type,
            'external_url' => $item['imdbID'] ? 'https://www.imdb.com/title/' . $item['imdbID'] : null,
            'cover_image_url' => $item['Poster'] !== 'N/A' ? $item['Poster'] : null,
            'bayesian_rating' => 0,
            'is_user_suggested' => false,
        ];
    }

    private function formatJikanItem(array $item, string $type): array
    {
        $source = ExternalSource::where('name', 'like', '%Jikan%')
            ->where('media_type', $type === 'anime' ? 'anime' : 'manga')
            ->first();

        // Extrair gêneros - genres, themes, demographics são arrays de objetos
        $genres = $item['genres'] ?? [];
        $themes = $item['themes'] ?? [];
        $demographics = $item['demographics'] ?? [];
        
        $allGenres = array_merge($genres, $themes, $demographics);
        $classifications = [];
        
        foreach ($allGenres as $genre) {
            // Cada item é um objeto com 'name'
            $genreName = is_array($genre) ? ($genre['name'] ?? '') : (is_object($genre) ? ($genre->name ?? '') : '');
            
            if (!empty($genreName) && isset(self::GENRE_MAP[$genreName])) {
                $classifications[] = self::GENRE_MAP[$genreName];
            }
        }
        
        // Fallback: usar rating como classificação se não houver gêneros
        if (empty($classifications) && !empty($item['rating'])) {
            $ratingMap = [
                'G - All Ages' => 'kids',
                'PG - Children' => 'kids',
                'PG-13 - Teens 13 or older' => 'shounen',
                'R - 17+ (violence & profanity)' => 'seinen',
                'R+ - Mild Nudity' => 'seinen',
                'Rx - Hentai' => 'hentai',
            ];
            $classifications[] = $ratingMap[$item['rating']] ?? 'general';
        }
        
        $classification = !empty($classifications) ? implode(',', array_unique($classifications)) : null;

        return [
            'source' => $source?->id,
            'external_id' => (string) $item['mal_id'],
            'title' => $item['title'],
            'title_english' => $item['title_english'] ?? null,
            'description' => $this->truncate($item['synopsis'] ?? '', 500),
            'type' => $type,
            'classification' => $classification,
            'external_url' => $item['url'] ?? null,
            'cover_image_url' => $item['images']['jpg']['image_url'] ?? null,
            'bayesian_rating' => ($item['score'] ?? 0) / 20,
            'is_user_suggested' => false,
        ];
    }

    private function formatAniListItem(array $item, string $type): array
    {
        $source = ExternalSource::where('name', 'AniList')
            ->where('media_type', $type)
            ->first();

        // Extrair gêneros do AniList
        $genres = $item['genres'] ?? [];
        $tags = $item['tags'] ?? [];
        
        $allGenres = array_merge($genres, $tags);
        $classifications = [];
        
        foreach ($allGenres as $genre) {
            $genreName = is_array($genre) ? ($genre['name'] ?? $genre['genre'] ?? '') : $genre;
            if (isset(self::GENRE_MAP[$genreName])) {
                $classifications[] = self::GENRE_MAP[$genreName];
            }
        }
        
        $classification = !empty($classifications) ? implode(',', array_unique($classifications)) : null;

        return [
            'source' => $source?->id,
            'external_id' => (string) $item['id'],
            'title' => $item['title']['romaji'] ?? '',
            'title_english' => $item['title']['english'] ?? null,
            'description' => $this->truncate(strip_tags($item['description'] ?? ''), 500),
            'type' => $type,
            'classification' => $classification,
            'external_url' => $item['siteUrl'] ?? null,
            'cover_image_url' => $item['coverImage']['large'] ?? $item['coverImage']['medium'] ?? null,
            'bayesian_rating' => ($item['averageScore'] ?? 0) / 20,
            'is_user_suggested' => false,
        ];
    }

    private function formatKitsuItem(array $item, string $type): array
    {
        $source = ExternalSource::where('name', 'Kitsu')
            ->where('media_type', $type)
            ->first();
        
        $attributes = $item['attributes'] ?? [];

        return [
            'source' => $source?->id,
            'external_id' => (string) $item['id'],
            'title' => $attributes['canonicalTitle'] ?? '',
            'title_english' => $attributes['titles']['en'] ?? null,
            'description' => $this->truncate($attributes['synopsis'] ?? '', 500),
            'type' => $type,
            'external_url' => config('app.url') . "/{$type}s/" . $item['id'],
            'cover_image_url' => $attributes['posterImage']['large'] ?? $attributes['posterImage']['medium'] ?? null,
            'bayesian_rating' => ((float) ($attributes['averageRating'] ?? 0)) / 20,
            'is_user_suggested' => false,
        ];
    }

    private function formatTMDBItem(array $item, string $type): array
    {
        $source = ExternalSource::where('name', 'like', '%TMDB%')
            ->where('media_type', $type)
            ->first();

        return [
            'source' => $source?->id,
            'external_id' => (string) $item['id'],
            'title' => $item['title'] ?? $item['name'] ?? '',
            'title_english' => null,
            'description' => $this->truncate($item['overview'] ?? '', 500),
            'type' => $type,
            'external_url' => 'https://www.themoviedb.org/' . ($type === 'movie' ? 'movie' : 'tv') . '/' . $item['id'],
            'cover_image_url' => $item['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $item['poster_path'] : null,
            'bayesian_rating' => ($item['vote_average'] ?? 0) / 2, // Convert 10-point to 5-point scale
            'is_user_suggested' => false,
        ];
    }

    private function truncate(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length - 3) . '...';
    }

    private function createWork(array $data, string $source): bool
    {
        // Check if work already exists by external_id (for API sources)
        if (!empty($data['external_id']) && !empty($data['source'])) {
            $exists = Work::where('external_id', $data['external_id'])
                ->where('external_source_id', $data['source'])
                ->exists();

            if ($exists) {
                return false;
            }
        }

        // Also check for duplicate title + type combination
        $existsByTitle = Work::whereRaw('LOWER(title) = ?', [strtolower($data['title'])])
            ->where('type', $data['type'])
            ->exists();

        if ($existsByTitle) {
            return false;
        }

        // Get the test user ID (owner role)
        $userId = \App\Models\User::join('user_role', 'users.id', '=', 'user_role.user_id')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->where('roles.name', 'owner')
            ->value('users.id');

        // Fallback to first user if no owner found
        if (!$userId) {
            $userId = \App\Models\User::value('id') ?? 1;
        }

        try {
            Work::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'classification' => $data['classification'] ?? null,
                'canonical_url' => $data['external_url'] ?? null,
                'external_source_id' => $data['source'],
                'external_id' => $data['external_id'],
                'external_url' => $data['external_url'] ?? null,
                'cover_image_url' => $data['cover_image_url'] ?? null,
                'bayesian_rating' => $data['bayesian_rating'] ?? 0,
                'user_id' => $userId,
                'is_user_suggested' => $data['is_user_suggested'] ?? false,
            ]);
            return true;
        } catch (\Exception $e) {
            $this->warn("Erro ao criar obra '{$data['title']}': " . $e->getMessage());
            return false;
        }
    }
}