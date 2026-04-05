<?php

namespace Database\Factories;

use App\Models\Interaction;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteFactory extends Factory
{
    protected $model = Vote::class;

    public function definition(): array
    {
        $votableType = fake()->randomElement([Topic::class, Interaction::class]);
        $votableId = $votableType === Topic::class
            ? Topic::factory()->create()->id
            : Interaction::factory()->create()->id;

        return [
            'user_id' => User::factory(),
            'votable_type' => $votableType,
            'votable_id' => $votableId,
            'is_upvote' => fake()->boolean(),
        ];
    }

    public function upvote(): static
    {
        return $this->state(fn (array $attributes) => ['is_upvote' => true]);
    }

    public function downvote(): static
    {
        return $this->state(fn (array $attributes) => ['is_upvote' => false]);
    }

    public function forTopic(Topic $topic): static
    {
        return $this->state(fn (array $attributes) => [
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
        ]);
    }

    public function forInteraction(Interaction $interaction): static
    {
        return $this->state(fn (array $attributes) => [
            'votable_type' => Interaction::class,
            'votable_id' => $interaction->id,
        ]);
    }
}
