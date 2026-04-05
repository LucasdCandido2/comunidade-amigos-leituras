<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug(2),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->randomElement(['🏆', '⭐', '📚', '💬', '🎯']),
            'category' => $this->faker->randomElement(['engagement', 'creation', 'rating']),
            'points' => $this->faker->numberBetween(5, 100),
            'criteria' => ['type' => 'topics_count', 'value' => 1],
        ];
    }
}
