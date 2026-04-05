<?php

namespace Database\Factories;

use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkFactory extends Factory
{
    protected $model = Work::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['book', 'manga', 'anime', 'comic', 'hq']),
            'canonical_url' => $this->faker->url(),
            'is_user_suggested' => $this->faker->boolean(),
            'bayesian_rating' => $this->faker->randomFloat(2, 0, 5),
            'user_id' => function () {
                return \App\Models\User::factory()->create()->id;
            },
        ];
    }
}