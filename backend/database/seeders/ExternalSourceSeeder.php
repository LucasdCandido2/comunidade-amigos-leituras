<?php

namespace Database\Seeders;

use App\Models\ExternalSource;
use Illuminate\Database\Seeder;

class ExternalSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Jikan (MyAnimeList)',
                'url' => 'https://myanimelist.net/',
                'type' => 'api',
                'api_endpoint' => 'https://api.jikan.moe/v4',
                'media_type' => 'anime',
                'is_active' => true,
            ],
            [
                'name' => 'Jikan (MyAnimeList)',
                'url' => 'https://myanimelist.net/',
                'type' => 'api',
                'api_endpoint' => 'https://api.jikan.moe/v4',
                'media_type' => 'manga',
                'is_active' => true,
            ],
            [
                'name' => 'AniList',
                'url' => 'https://anilist.co/',
                'type' => 'api',
                'api_endpoint' => 'https://graphql.anilist.co',
                'media_type' => 'anime',
                'is_active' => true,
            ],
            [
                'name' => 'Kitsu',
                'url' => 'https://kitsu.io/',
                'type' => 'api',
                'api_endpoint' => 'https://kitsu.io/api/edge',
                'media_type' => 'anime',
                'is_active' => true,
            ],
            [
                'name' => 'TheMovieDB (TMDB)',
                'url' => 'https://www.themoviedb.org/',
                'type' => 'api',
                'api_endpoint' => 'https://api.themoviedb.org/3',
                'media_type' => 'anime',
                'is_active' => true,
            ],
            [
                'name' => 'OMDB API',
                'url' => 'https://www.omdbapi.com/',
                'type' => 'api',
                'api_endpoint' => 'https://www.omdbapi.com/',
                'media_type' => 'movie',
                'is_active' => true,
            ],
            [
                'name' => 'ComicVine',
                'url' => 'https://comicvine.gamespot.com/',
                'type' => 'api',
                'api_endpoint' => 'https://comicvine.gamespot.com/api/',
                'media_type' => 'comic',
                'is_active' => true,
            ],
        ];

        foreach ($sources as $source) {
            ExternalSource::updateOrCreate(
                ['name' => $source['name'], 'media_type' => $source['media_type']],
                $source
            );
        }

        $this->command->info('✅ Fontes externas criadas com sucesso!');
        $this->command->info('📡 Fontes: Jikan, AniList, Kitsu, TMDB, OMDB, ComicVine');
    }
}