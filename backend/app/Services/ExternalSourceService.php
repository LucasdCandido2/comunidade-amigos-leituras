<?php

namespace App\Services;

use App\Models\ExternalSource;
use App\Models\Work;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalSourceService
{
    private const JIKAN_BASE = 'https://api.jikan.moe/v4';
    private const ANILIST_ENDPOINT = 'https://graphql.anilist.co';
    private const KITSU_BASE = 'https://kitsu.io/api/edge';
    private const TMDB_BASE = 'https://api.themoviedb.org/3';

    public function searchExternal(string $query, string $mediaType, ?string $source = null): array
    {
        $source = $source ?? 'jikan';
        
        return match($source) {
            'anilist' => $this->searchAniList($query, $mediaType),
            'kitsu' => $this->searchKitsu($query, $mediaType),
            'tmdb' => $this->searchTMDB($query, $mediaType),
            default => $this->searchJikan($query, $mediaType),
        };
    }

    public function fetchDetails(string $externalId, string $mediaType, ?string $source = null): ?array
    {
        $source = $source ?? 'jikan';
        
        return match($source) {
            'anilist' => $this->fetchAniList($externalId, $mediaType),
            'kitsu' => $this->fetchKitsu($externalId, $mediaType),
            'tmdb' => $this->fetchTMDB($externalId, $mediaType),
            default => $this->fetchJikan($externalId, $mediaType),
        };
    }

    public function getExternalSources(): array
    {
        return [
            'jikan' => [
                'name' => 'Jikan (MyAnimeList)',
                'base_url' => self::JIKAN_BASE,
                'media_types' => ['anime', 'manga'],
            ],
            'anilist' => [
                'name' => 'AniList',
                'base_url' => self::ANILIST_ENDPOINT,
                'media_types' => ['anime', 'manga'],
            ],
            'kitsu' => [
                'name' => 'Kitsu',
                'base_url' => self::KITSU_BASE,
                'media_types' => ['anime', 'manga'],
            ],
            'tmdb' => [
                'name' => 'TheMovieDB (TMDB)',
                'base_url' => self::TMDB_BASE,
                'media_types' => ['anime', 'movie', 'series'],
            ],
        ];
    }

    private function searchJikan(string $query, string $mediaType): array
    {
        $type = $mediaType === 'anime' ? 'anime' : 'manga';
        
        $response = Http::timeout(10)->get(self::JIKAN_BASE . '/search', [
            'q' => $query,
            'type' => $type,
            'limit' => 10,
        ]);

        if (!$response->successful()) {
            Log::error('Jikan API error', ['response' => $response->json()]);
            return [];
        }

        return array_map(fn($item) => $this->formatJikanSearch($item, $mediaType), $response->json('data', []));
    }

    private function fetchJikan(string $externalId, string $mediaType): ?array
    {
        $endpoint = $mediaType === 'anime' ? 'anime' : 'manga';
        
        $response = Http::timeout(10)->get(self::JIKAN_BASE . "/{$endpoint}/{$externalId}");

        if (!$response->successful()) {
            Log::error('Jikan API error', ['response' => $response->json()]);
            return null;
        }

        return $this->formatJikanDetails($response->json('data', []), $mediaType);
    }

    private function searchAniList(string $query, string $mediaType): array
    {
        $type = strtoupper($mediaType);
        
        $response = Http::timeout(10)->post(self::ANILIST_ENDPOINT, [
            'query' => '
                query ($search: String, $type: MediaType) {
                    Page(perPage: 10) {
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
                            popularity
                        }
                    }
                }
            ',
            'variables' => ['search' => $query, 'type' => $type],
        ]);

        if (!$response->successful()) {
            Log::error('AniList API error', ['response' => $response->json()]);
            return [];
        }

        return array_map(fn($item) => $this->formatAniListSearch($item, $mediaType), $response->json('data.Page.media', []));
    }

    private function fetchAniList(string $externalId, string $mediaType): ?array
    {
        $type = strtoupper($mediaType);
        
        $response = Http::timeout(10)->post(self::ANILIST_ENDPOINT, [
            'query' => '
                query ($id: Int, $type: MediaType) {
                    Media(id: $id, type: $type) {
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
                        popularity
                        source
                        duration
                        startDate { year month day }
                        endDate { year month day }
                        studios { nodes { name } }
                        genres
                        externalLinks { url site }
                    }
                }
            ',
            'variables' => ['id' => (int)$externalId, 'type' => $type],
        ]);

        if (!$response->successful()) {
            Log::error('AniList API error', ['response' => $response->json()]);
            return null;
        }

        return $this->formatAniListDetails($response->json('data.Media', []), $mediaType);
    }

    private function searchKitsu(string $query, string $mediaType): array
    {
        $type = $mediaType === 'anime' ? 'anime' : 'manga';
        
        $response = Http::timeout(10)->get(self::KITSU_BASE . '/anime', [
            'filter[title]' => $query,
            'page[limit]' => 10,
        ]);

        if (!$response->successful()) {
            Log::error('Kitsu API error', ['response' => $response->json()]);
            return [];
        }

        return array_map(fn($item) => $this->formatKitsuSearch($item, 'anime'), $response->json('data', []));
    }

    private function fetchKitsu(string $externalId, string $mediaType): ?array
    {
        $type = $mediaType === 'anime' ? 'anime' : 'manga';
        
        $response = Http::timeout(10)->get(self::KITSU_BASE . "/{$type}s/{$externalId}");

        if (!$response->successful()) {
            Log::error('Kitsu API error', ['response' => $response->json()]);
            return null;
        }

        return $this->formatKitsuDetails($response->json('data', []), $mediaType);
    }

    private function searchTMDB(string $query, string $mediaType): array
    {
        $searchType = $mediaType === 'anime' ? 'tv' : ($mediaType === 'movie' ? 'movie' : 'tv');
        
        $response = Http::timeout(10)->get(self::TMDB_BASE . '/search/' . $searchType, [
            'query' => $query,
            'api_key' => config('services.tmdb.api_key', ''),
        ]);

        if (!$response->successful()) {
            Log::error('TMDB API error', ['response' => $response->json()]);
            return [];
        }

        return array_map(fn($item) => $this->formatTMDBSearch($item, $mediaType), $response->json('results', []));
    }

    private function fetchTMDB(string $externalId, string $mediaType): ?array
    {
        $searchType = $mediaType === 'movie' ? 'movie' : 'tv';
        
        $response = Http::timeout(10)->get(self::TMDB_BASE . "/{$searchType}/{$externalId}", [
            'api_key' => config('services.tmdb.api_key', ''),
        ]);

        if (!$response->successful()) {
            Log::error('TMDB API error', ['response' => $response->json()]);
            return null;
        }

        return $this->formatTMDBDetails($response->json(), $mediaType);
    }

    private function formatJikanSearch(array $item, string $mediaType): array
    {
        return [
            'source' => 'jikan',
            'external_id' => (string)$item['mal_id'],
            'title' => $item['title'],
            'title_english' => $item['title_english'] ?? null,
            'title_japanese' => $item['title_japanese'] ?? null,
            'synopsis' => $this->truncate($item['synopsis'] ?? '', 500),
            'image_url' => $item['images']['jpg']['image_url'] ?? null,
            'url' => $item['url'],
            'type' => $item['type'] ?? null,
            'episodes' => $item['episodes'] ?? null,
            'chapters' => $item['chapters'] ?? null,
            'status' => $item['status'] ?? null,
            'score' => $item['score'] ?? null,
            'media_type' => $mediaType,
        ];
    }

    private function formatJikanDetails(array $item, string $mediaType): array
    {
        $references = $this->buildReferences([
            'MyAnimeList' => $item['url'] ?? null,
            'Official Site' => $item['external'] ?? [],
        ], 'jikan');

        return [
            'source' => 'jikan',
            'external_id' => (string)$item['mal_id'],
            'title' => $item['title'],
            'title_english' => $item['title_english'] ?? null,
            'title_japanese' => $item['title_japanese'] ?? null,
            'synopsis' => $item['synopsis'] ?? '',
            'background' => $item['background'] ?? '',
            'image_url' => $item['images']['jpg']['large_image_url'] ?? $item['images']['jpg']['image_url'] ?? null,
            'url' => $item['url'],
            'type' => $item['type'] ?? null,
            'episodes' => $item['episodes'] ?? null,
            'chapters' => $item['chapters'] ?? null,
            'status' => $item['status'] ?? null,
            'rating' => $item['rating'] ?? null,
            'score' => $item['score'] ?? null,
            'rank' => $item['rank'] ?? null,
            'popularity' => $item['popularity'] ?? null,
            'media_type' => $mediaType,
            'source_info' => $item['source'] ?? null,
            'duration' => $item['duration'] ?? null,
            'studios' => array_column($item['studios'] ?? [], 'name'),
            'genres' => array_column($item['genres'] ?? [], 'name'),
            'external_references' => $references,
        ];
    }

    private function formatAniListSearch(array $item, string $mediaType): array
    {
        return [
            'source' => 'anilist',
            'external_id' => (string)$item['id'],
            'title' => $item['title']['romaji'] ?? '',
            'title_english' => $item['title']['english'] ?? null,
            'title_japanese' => $item['title']['native'] ?? null,
            'synopsis' => $this->truncate(strip_tags($item['description'] ?? ''), 500),
            'image_url' => $item['coverImage']['large'] ?? $item['coverImage']['medium'] ?? null,
            'url' => $item['siteUrl'],
            'type' => $item['format'] ?? null,
            'episodes' => $item['episodes'] ?? null,
            'chapters' => $item['chapters'] ?? null,
            'status' => $item['status'] ?? null,
            'score' => $item['averageScore'] ?? null,
            'media_type' => $mediaType,
        ];
    }

    private function formatAniListDetails(array $item, string $mediaType): ?array
    {
        if (empty($item)) {
            return null;
        }

        $references = $this->buildReferences([
            'AniList' => $item['siteUrl'] ?? null,
            'Official Site' => null,
        ], 'anilist');

        if (!empty($item['externalLinks'])) {
            foreach ($item['externalLinks'] as $link) {
                $references[$link['site']] = $link['url'];
            }
        }

        return [
            'source' => 'anilist',
            'external_id' => (string)$item['id'],
            'title' => $item['title']['romaji'] ?? '',
            'title_english' => $item['title']['english'] ?? null,
            'title_japanese' => $item['title']['native'] ?? null,
            'synopsis' => strip_tags($item['description'] ?? ''),
            'image_url' => $item['coverImage']['large'] ?? $item['coverImage']['medium'] ?? null,
            'url' => $item['siteUrl'],
            'type' => $item['format'] ?? null,
            'episodes' => $item['episodes'] ?? null,
            'chapters' => $item['chapters'] ?? null,
            'status' => $item['status'] ?? null,
            'score' => $item['averageScore'] ?? null,
            'popularity' => $item['popularity'] ?? null,
            'media_type' => $mediaType,
            'source_info' => $item['source'] ?? null,
            'duration' => $item['duration'] ?? null,
            'studios' => array_column($item['studios']['nodes'] ?? [], 'name'),
            'genres' => $item['genres'] ?? [],
            'external_references' => $references,
        ];
    }

    private function formatKitsuSearch(array $item, string $mediaType): array
    {
        $attributes = $item['attributes'] ?? [];
        
        return [
            'source' => 'kitsu',
            'external_id' => (string)$item['id'],
            'title' => $attributes['canonicalTitle'] ?? '',
            'title_english' => $attributes['titles']['en'] ?? null,
            'title_japanese' => $attributes['titles']['ja_jp'] ?? null,
            'synopsis' => $this->truncate($attributes['synopsis'] ?? '', 500),
            'image_url' => $attributes['posterImage']['large'] ?? $attributes['posterImage']['medium'] ?? null,
            'url' => config('app.url') . '/' . $mediaType . 's/' . $item['id'],
            'type' => $attributes['subtype'] ?? null,
            'episodes' => $attributes['episodeCount'] ?? null,
            'chapters' => $attributes['chapterCount'] ?? null,
            'status' => $attributes['status'] ?? null,
            'score' => $attributes['averageRating'] ?? null,
            'media_type' => $mediaType,
        ];
    }

    private function formatKitsuDetails(array $item, string $mediaType): ?array
    {
        if (empty($item)) {
            return null;
        }

        $attributes = $item['attributes'] ?? [];
        
        $references = $this->buildReferences([
            'Kitsu' => config('app.url') . '/' . $mediaType . 's/' . $item['id'],
        ], 'kitsu');

        return [
            'source' => 'kitsu',
            'external_id' => (string)$item['id'],
            'title' => $attributes['canonicalTitle'] ?? '',
            'title_english' => $attributes['titles']['en'] ?? null,
            'title_japanese' => $attributes['titles']['ja_jp'] ?? null,
            'synopsis' => $attributes['synopsis'] ?? '',
            'image_url' => $attributes['posterImage']['large'] ?? $attributes['posterImage']['medium'] ?? null,
            'url' => config('app.url') . '/' . $mediaType . 's/' . $item['id'],
            'type' => $attributes['subtype'] ?? null,
            'episodes' => $attributes['episodeCount'] ?? null,
            'chapters' => $attributes['chapterCount'] ?? null,
            'status' => $attributes['status'] ?? null,
            'score' => $attributes['averageRating'] ?? null,
            'media_type' => $mediaType,
            'external_references' => $references,
        ];
    }

    private function formatTMDBSearch(array $item, string $mediaType): array
    {
        return [
            'source' => 'tmdb',
            'external_id' => (string)$item['id'],
            'title' => $item['title'] ?? $item['name'] ?? '',
            'title_english' => null,
            'title_japanese' => null,
            'synopsis' => $this->truncate($item['overview'] ?? '', 500),
            'image_url' => $item['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $item['poster_path'] : null,
            'url' => 'https://www.themoviedb.org/' . ($mediaType === 'movie' ? 'movie' : 'tv') . '/' . $item['id'],
            'type' => $mediaType,
            'episodes' => null,
            'chapters' => null,
            'status' => $item['release_date'] ?? $item['first_air_date'] ?? null,
            'score' => $item['vote_average'] ? round($item['vote_average'] * 10) : null,
            'media_type' => $mediaType,
        ];
    }

    private function formatTMDBDetails(array $item, string $mediaType): ?array
    {
        if (empty($item)) {
            return null;
        }

        $references = $this->buildReferences([
            'TMDB' => 'https://www.themoviedb.org/' . ($mediaType === 'movie' ? 'movie' : 'tv') . '/' . $item['id'],
            'IMDb' => $item['imdb_id'] ? 'https://www.imdb.com/title/' . $item['imdb_id'] : null,
        ], 'tmdb');

        return [
            'source' => 'tmdb',
            'external_id' => (string)$item['id'],
            'title' => $item['title'] ?? $item['name'] ?? '',
            'title_english' => null,
            'title_japanese' => null,
            'synopsis' => $item['overview'] ?? '',
            'image_url' => $item['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $item['poster_path'] : null,
            'url' => 'https://www.themoviedb.org/' . ($mediaType === 'movie' ? 'movie' : 'tv') . '/' . $item['id'],
            'type' => $mediaType,
            'episodes' => $item['number_of_episodes'] ?? null,
            'chapters' => null,
            'status' => $item['status'] ?? null,
            'score' => $item['vote_average'] ? round($item['vote_average'] * 10) : null,
            'media_type' => $mediaType,
            'genres' => array_column($item['genres'] ?? [], 'name'),
            'external_references' => $references,
        ];
    }

    private function buildReferences(array $links, string $source): array
    {
        $references = [];
        foreach ($links as $name => $url) {
            if ($url) {
                $references[$name] = $url;
            }
        }
        return $references;
    }

    private function truncate(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length - 3) . '...';
    }

    public function syncWorkWithExternal(Work $work): ?Work
    {
        if (!$work->external_id || !$work->externalSource) {
            return null;
        }

        $source = match($work->externalSource->name) {
            'AniList' => 'anilist',
            'Kitsu' => 'kitsu',
            'TheMovieDB (TMDB)' => 'tmdb',
            default => 'jikan',
        };

        $details = $this->fetchDetails($work->external_id, $work->type, $source);
        
        if (!$details) {
            return null;
        }

        $updateData = [
            'title' => $details['title'] ?? $work->title,
            'description' => $details['synopsis'] ?? $work->description,
            'cover_image_url' => $details['image_url'] ?? $work->cover_image_url,
            'external_url' => $details['url'] ?? $work->external_url,
            'external_references' => $details['external_references'] ?? null,
        ];

        $work->update($updateData);

        return $work->fresh();
    }
}