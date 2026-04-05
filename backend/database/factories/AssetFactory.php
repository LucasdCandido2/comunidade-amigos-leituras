<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $isImage = $this->faker->boolean(70);
        $extension = $isImage ? $this->faker->randomElement(['jpg', 'png', 'gif', 'webp']) : 'pdf';
        $mimeType = match ($extension) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
        };

        return [
            'user_id' => User::factory(),
            'topic_id' => Topic::factory(),
            'original_name' => $this->faker->word() . '.' . $extension,
            'stored_name' => Str::uuid() . '.' . $extension,
            'mime_type' => $mimeType,
            'size' => $this->faker->numberBetween(1024, 5242880),
            'path' => 'assets/topics/' . $this->faker->numberBetween(1, 100) . '/' . Str::uuid() . '.' . $extension,
            'disk' => 'local',
            'is_image' => $isImage,
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/jpeg',
            'is_image' => true,
        ]);
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'application/pdf',
            'is_image' => false,
            'original_name' => $this->faker->word() . '.pdf',
        ]);
    }
}
