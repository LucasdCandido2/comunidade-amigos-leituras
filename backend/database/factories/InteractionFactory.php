<?php

namespace Database\Factories;

use App\Models\Interaction;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

class InteractionFactory extends Factory
{
    protected $model = Interaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'topic_id' => Topic::factory(),
            'content' => $this->faker->paragraph(),
            'rating' => $this->faker->optional(0.7)->numberBetween(1, 5),
            'is_visible' => true,
        ];
    }
}
