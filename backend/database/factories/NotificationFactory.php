<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $types = ['new_comment', 'new_topic', 'new_rating'];
        $type = $this->faker->randomElement($types);

        $titles = [
            'new_comment' => 'Novo comentário no seu tópico',
            'new_topic' => 'Novo tópico na sua obra',
            'new_rating' => 'Nova avaliação na sua obra',
        ];

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'title' => $titles[$type],
            'message' => $this->faker->sentence(10),
            'data' => ['test' => true],
            'is_read' => $this->faker->boolean(30),
            'read_at' => fn(array $attributes) => $attributes['is_read'] ? now() : null,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
