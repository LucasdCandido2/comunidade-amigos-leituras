<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Models\Work;
use App\Models\User;
use App\Models\Interaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Work $work;
    protected Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->work = Work::factory()->create();
        $this->topic = Topic::factory()->create([
            'work_id' => $this->work->id,
            'user_id' => $this->user->id
        ]);
    }

    protected function actingAsWithToken($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer $token");
    }

    // ===== CREATE TESTS =====

    public function test_create_topic_with_valid_data()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/topics', [
                'title' => 'My Experience',
                'content' => 'This was a great book!',
                'work_id' => $this->work->id,
                'rating' => 5
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'title', 'content', 'user_id']);
        $this->assertDatabaseHas('topics', [
            'title' => 'My Experience',
            'user_id' => $this->user->id
        ]);
    }

    public function test_create_topic_requires_authentication()
    {
        $response = $this->postJson('/api/topics', [
            'title' => 'Test',
            'content' => 'Test',
            'work_id' => $this->work->id
        ]);

        $response->assertStatus(401);
    }

    public function test_create_topic_validates_title_required()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/topics', [
                'content' => 'Test',
                'work_id' => $this->work->id
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_create_topic_validates_content_minimum_length()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/topics', [
                'title' => 'Test',
                'content' => 'ab', // Less than 10 chars
                'work_id' => $this->work->id
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('content');
    }

    public function test_create_topic_validates_work_exists()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/topics', [
                'title' => 'Test',
                'content' => 'Test content here',
                'work_id' => 99999
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('work_id');
    }

    // ===== READ TESTS =====

    public function test_list_all_topics()
    {
        Topic::factory()->count(5)->create();

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/topics');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(6, count($response->json('data') ?? $response->json()));
    }

    public function test_get_single_topic()
    {
        $response = $this->actingAsWithToken($this->user)
            ->getJson("/api/topics/{$this->topic->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->topic->id,
            'title' => $this->topic->title
        ]);
    }

    public function test_get_topic_returns_404_if_not_found()
    {
        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/topics/99999');

        $response->assertStatus(404);
    }

    // ===== UPDATE TESTS =====

    public function test_update_topic_with_valid_data()
    {
        $response = $this->actingAsWithToken($this->user)
            ->putJson("/api/topics/{$this->topic->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated content for the topic',
                'rating' => 4
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('topics', [
            'id' => $this->topic->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_update_topic_requires_authentication()
    {
        $response = $this->putJson("/api/topics/{$this->topic->id}", [
            'title' => 'Updated'
        ]);

        $response->assertStatus(401);
    }

    public function test_update_topic_returns_404_if_not_found()
    {
        $response = $this->actingAsWithToken($this->user)
            ->putJson('/api/topics/99999', [
                'title' => 'Updated'
            ]);

        $response->assertStatus(404);
    }

    public function test_update_topic_validates_ownership()
    {
        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $otherToken")
            ->putJson("/api/topics/{$this->topic->id}", [
                'title' => 'Hacked Title'
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('topics', [
            'id' => $this->topic->id,
            'title' => 'Hacked Title'
        ]);
    }

    public function test_update_topic_validates_content_length()
    {
        $response = $this->actingAsWithToken($this->user)
            ->putJson("/api/topics/{$this->topic->id}", [
                'content' => 'ab' // Less than 10 chars
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('content');
    }

    public function test_update_topic_allows_partial_updates()
    {
        $originalContent = $this->topic->content;

        $response = $this->actingAsWithToken($this->user)
            ->putJson("/api/topics/{$this->topic->id}", [
                'title' => 'New Title'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('topics', [
            'id' => $this->topic->id,
            'title' => 'New Title',
            'content' => $originalContent
        ]);
    }

    // ===== DELETE TESTS =====

    public function test_delete_topic_soft_deletes()
    {
        $response = $this->actingAsWithToken($this->user)
            ->deleteJson("/api/topics/{$this->topic->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('topics', ['id' => $this->topic->id]);
    }

    public function test_delete_topic_requires_authentication()
    {
        $response = $this->deleteJson("/api/topics/{$this->topic->id}");

        $response->assertStatus(401);
    }

    public function test_delete_topic_validates_ownership()
    {
        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $otherToken")
            ->deleteJson("/api/topics/{$this->topic->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('topics', ['id' => $this->topic->id]);
    }

    public function test_delete_topic_returns_404_if_not_found()
    {
        $response = $this->actingAsWithToken($this->user)
            ->deleteJson('/api/topics/99999');

        $response->assertStatus(404);
    }

    public function test_delete_topic_preserves_interactions()
    {
        $interaction = Interaction::factory()->create(['topic_id' => $this->topic->id]);

        $this->actingAsWithToken($this->user)
            ->deleteJson("/api/topics/{$this->topic->id}");

        $this->assertDatabaseHas('interactions', [
            'id' => $interaction->id,
            'topic_id' => $this->topic->id
        ]);
    }

    public function test_list_excludes_soft_deleted_topics()
    {
        $this->topic->delete();

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/topics');

        $data = $response->json('data') ?? $response->json();
        $ids = array_column($data, 'id');
        $this->assertNotContains($this->topic->id, $ids);
    }
}
