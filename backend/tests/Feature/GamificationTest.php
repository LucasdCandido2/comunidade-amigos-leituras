<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Badge::factory()->count(5)->create();
    }

    protected function actingAsWithToken($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer $token");
    }

    public function test_user_can_get_stats()
    {
        $this->user->update([
            'reputation' => 0,
            'topics_count' => 0,
            'interactions_count' => 0,
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/gamification/stats');

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stats' => [
                'reputation',
                'level',
                'topics_count',
                'interactions_count',
                'badges_count',
                'works_count',
            ],
            'badges',
        ]);
    }

    public function test_user_stats_requires_authentication()
    {
        $response = $this->getJson('/api/gamification/stats');

        $response->assertStatus(401);
    }

    public function test_leaderboard_is_public()
    {
        $response = $this->getJson('/api/leaderboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'reputation',
                'level',
                'badges_count',
            ]
        ]);
    }

    public function test_badges_are_public()
    {
        $response = $this->getJson('/api/badges');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'slug',
                'description',
                'icon',
                'category',
                'points',
            ]
        ]);
    }

    public function test_reputation_level_calculation()
    {
        $this->user->update(['reputation' => 150]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/gamification/stats');

        $response->assertStatus(200);
        $this->assertEquals('Avançado ⭐', $response->json('stats.level'));
    }

    public function test_reputation_level_legend()
    {
        $this->user->update(['reputation' => 1000]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/gamification/stats');

        $response->assertStatus(200);
        $this->assertEquals('Lenda 📚', $response->json('stats.level'));
    }

    public function test_reputation_level_beginner()
    {
        $this->user->update(['reputation' => 10]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/gamification/stats');

        $response->assertStatus(200);
        $this->assertEquals('Iniciante 🌱', $response->json('stats.level'));
    }
}
