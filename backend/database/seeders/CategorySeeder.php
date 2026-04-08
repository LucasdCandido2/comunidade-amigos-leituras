<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Ação', 'slug' => 'acao', 'color' => '#ef4444'],
            ['name' => 'Aventura', 'slug' => 'aventura', 'color' => '#f97316'],
            ['name' => 'Comédia', 'slug' => 'comedia', 'color' => '#eab308'],
            ['name' => 'Drama', 'slug' => 'drama', 'color' => '#a855f7'],
            ['name' => 'Fantasia', 'slug' => 'fantasia', 'color' => '#22c55e'],
            ['name' => 'Ficção Científica', 'slug' => 'ficcao-cientifica', 'color' => '#06b6d4'],
            ['name' => 'Horror', 'slug' => 'horror', 'color' => '#dc2626'],
            ['name' => 'Mistério', 'slug' => 'misterio', 'color' => '#6366f1'],
            ['name' => 'Romance', 'slug' => 'romance', 'color' => '#ec4899'],
            ['name' => 'Slice of Life', 'slug' => 'slice-of-life', 'color' => '#14b8a6'],
            ['name' => 'Esporte', 'slug' => 'esporte', 'color' => '#f59e0b'],
            ['name' => 'Música', 'slug' => 'musica', 'color' => '#8b5cf6'],
            ['name' => 'Mecha', 'slug' => 'mecha', 'color' => '#64748b'],
            ['name' => 'Isekai', 'slug' => 'isekai', 'color' => '#0ea5e9'],
            ['name' => 'Shoujo', 'slug' => 'shoujo', 'color' => '#f472b6'],
            ['name' => 'Shonen', 'slug' => 'shonen', 'color' => '#3b82f6'],
            ['name' => 'Seinen', 'slug' => 'seinen', 'color' => '#71717a'],
            ['name' => 'Josei', 'slug' => 'josei', 'color' => '#d8b4fe'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
