<?php

namespace Tests\Feature;

use App\Models\Interaction;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_user_can_upvote_topic(): void
    {
        $topic = Topic::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/vote/topics/{$topic->id}", ['is_upvote' => true]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'action' => 'created',
            ]);

        $this->assertDatabaseHas('votes', [
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => true,
        ]);
    }

    public function test_user_can_downvote_topic(): void
    {
        $topic = Topic::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/vote/topics/{$topic->id}", ['is_upvote' => false]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'action' => 'created',
            ]);

        $this->assertDatabaseHas('votes', [
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => false,
        ]);
    }

    public function test_user_can_vote_on_interaction(): void
    {
        $topic = Topic::factory()->create();
        $interaction = Interaction::factory()->create(['topic_id' => $topic->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/vote/interactions/{$interaction->id}", ['is_upvote' => true]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'action' => 'created',
            ]);

        $this->assertDatabaseHas('votes', [
            'user_id' => $this->user->id,
            'votable_type' => Interaction::class,
            'votable_id' => $interaction->id,
            'is_upvote' => true,
        ]);
    }

    public function test_user_can_remove_vote(): void
    {
        $topic = Topic::factory()->create();
        Vote::factory()->create([
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/vote/topics/{$topic->id}", ['is_upvote' => true]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'action' => 'removed',
            ]);

        $this->assertDatabaseMissing('votes', [
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
        ]);
    }

    public function test_user_can_change_vote(): void
    {
        $topic = Topic::factory()->create();
        Vote::factory()->create([
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/vote/topics/{$topic->id}", ['is_upvote' => false]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'action' => 'changed',
            ]);

        $this->assertDatabaseHas('votes', [
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => false,
        ]);
    }

    public function test_cooldown_prevents_duplicate_votes(): void
    {
        $topic = Topic::factory()->create();
        $cacheKey = "vote_cooldown:{$this->user->id}:" . Topic::class . ":{$topic->id}";
        Cache::put($cacheKey, 60, 60);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/vote/topics/{$topic->id}", ['is_upvote' => true]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'Aguarde para votar novamente',
            ]);
    }

    public function test_user_cannot_vote_without_auth(): void
    {
        $topic = Topic::factory()->create();

        $response = $this->postJson("/api/vote/topics/{$topic->id}", ['is_upvote' => true]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_vote_on_nonexistent_topic(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/vote/topics/99999', ['is_upvote' => true]);

        $response->assertStatus(404);
    }

    public function test_user_cannot_vote_on_invalid_type(): void
    {
        $topic = Topic::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/vote/invalid/{$topic->id}", ['is_upvote' => true]);

        $response->assertStatus(400);
    }

    public function test_get_vote_status(): void
    {
        $topic = Topic::factory()->create();
        Vote::factory()->create([
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/vote/topics/{$topic->id}/status");

        $response->assertOk()
            ->assertJson([
                'user_vote' => true,
            ]);
    }

    public function test_vote_count_calculates_correctly(): void
    {
        $topic = Topic::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        Vote::factory()->create(['votable_type' => Topic::class, 'votable_id' => $topic->id, 'is_upvote' => true]);
        Vote::factory()->create(['user_id' => $user2->id, 'votable_type' => Topic::class, 'votable_id' => $topic->id, 'is_upvote' => true]);
        Vote::factory()->create(['user_id' => $user3->id, 'votable_type' => Topic::class, 'votable_id' => $topic->id, 'is_upvote' => false]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/vote/topics/{$topic->id}/status");

        $response->assertOk()
            ->assertJson(['vote_count' => 1]);
    }

    public function test_each_user_can_only_vote_once_per_item(): void
    {
        $topic = Topic::factory()->create();

        Vote::factory()->create([
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => true,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Vote::factory()->create([
            'user_id' => $this->user->id,
            'votable_type' => Topic::class,
            'votable_id' => $topic->id,
            'is_upvote' => true,
        ]);
    }
}
