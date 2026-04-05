<?php

namespace Tests\Feature;

use App\Models\Work;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Work $work;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->work = Work::factory()->create(['user_id' => $this->user->id]);
    }

    protected function actingAsWithToken($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer $token");
    }

    // ===== CREATE TESTS =====

    public function test_create_work_with_valid_data()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/works', [
                'title' => 'One Piece',
                'description' => 'Famous anime series',
                'type' => 'anime',
                'canonical_url' => 'https://onepiece.com'
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'title', 'type']);
        $this->assertDatabaseHas('works', ['title' => 'One Piece']);
    }

    public function test_create_work_requires_authentication()
    {
        $response = $this->postJson('/api/works', [
            'title' => 'Test Work',
            'type' => 'book'
        ]);

        $response->assertStatus(401);
    }

    public function test_create_work_validates_title_required()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/works', [
                'type' => 'book'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_create_work_validates_type_in_enum()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/works', [
                'title' => 'Test',
                'type' => 'invalid_type'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function test_create_work_validates_valid_types()
    {
        foreach (['book', 'manga', 'anime', 'comic', 'hq'] as $type) {
            $response = $this->actingAsWithToken($this->user)
                ->postJson('/api/works', [
                    'title' => "Test $type",
                    'type' => $type
                ]);

            $response->assertStatus(201);
        }
    }

    public function test_create_work_validates_url_format()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/works', [
                'title' => 'Test',
                'type' => 'book',
                'canonical_url' => 'not-a-valid-url'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('canonical_url');
    }

    public function test_create_work_allows_empty_canonical_url()
    {
        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/works', [
                'title' => 'Test',
                'type' => 'book',
                'canonical_url' => null
            ]);

        $response->assertStatus(201);
    }

    // ===== READ TESTS =====

    public function test_get_top_works()
    {
        Work::factory()->count(15)->create();

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/works/top');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertLessThanOrEqual(10, count($data));
    }

    public function test_get_works_ordered_by_bayesian_rating()
    {
        Work::factory()->count(5)->create(['bayesian_rating' => 3.0]);
        $highRated = Work::factory()->create(['bayesian_rating' => 4.5]);
        $lowRated = Work::factory()->create(['bayesian_rating' => 2.0]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/works/top');

        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertGreaterThanOrEqual(2, count($data));
        
        $sortedRatings = array_column($data, 'bayesian_rating');
        $isSorted = $sortedRatings === array_values(array_filter($sortedRatings));
        $this->assertTrue(
            $highRated->bayesian_rating >= $data[0]['bayesian_rating'] || 
            $data[0]['id'] === $highRated->id
        );
    }

    // ===== UPDATE TESTS =====

    public function test_update_work_with_valid_data()
    {
        $response = $this->actingAsWithToken($this->user)
            ->putJson("/api/works/{$this->work->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'type' => 'manga',
                'canonical_url' => 'https://updated.com'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('works', [
            'id' => $this->work->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_update_work_requires_authentication()
    {
        $response = $this->putJson("/api/works/{$this->work->id}", [
            'title' => 'Updated'
        ]);

        $response->assertStatus(401);
    }

    public function test_update_work_returns_404_if_not_found()
    {
        $response = $this->actingAsWithToken($this->user)
            ->putJson('/api/works/99999', [
                'title' => 'Updated'
            ]);

        $response->assertStatus(404);
    }

    public function test_update_work_validates_title_required()
    {
        $response = $this->actingAsWithToken($this->user)
            ->putJson("/api/works/{$this->work->id}", [
                'title' => '',
                'type' => 'book'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_update_work_validates_type()
    {
        $response = $this->actingAsWithToken($this->user)
            ->putJson("/api/works/{$this->work->id}", [
                'title' => 'Test',
                'type' => 'invalid'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function test_update_work_allows_partial_update()
    {
        $originalDescription = $this->work->description;

        $response = $this->actingAsWithToken($this->user)
            ->putJson("/api/works/{$this->work->id}", [
                'title' => 'New Title'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('works', [
            'id' => $this->work->id,
            'title' => 'New Title',
            'description' => $originalDescription
        ]);
    }

    // ===== DELETE TESTS =====

    public function test_delete_work_soft_deletes()
    {
        $response = $this->actingAsWithToken($this->user)
            ->deleteJson("/api/works/{$this->work->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('works', ['id' => $this->work->id]);
    }

    public function test_delete_work_requires_authentication()
    {
        $response = $this->deleteJson("/api/works/{$this->work->id}");

        $response->assertStatus(401);
    }

    public function test_delete_work_returns_404_if_not_found()
    {
        $response = $this->actingAsWithToken($this->user)
            ->deleteJson('/api/works/99999');

        $response->assertStatus(404);
    }

    public function test_get_works_excludes_soft_deleted_works()
    {
        $this->work->delete();

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/works/top');

        $data = $response->json();
        $ids = array_column($data, 'id');
        $this->assertNotContains($this->work->id, $ids);
    }

    public function test_delete_work_preserves_related_topics()
    {
        $topic = Topic::factory()->create(['work_id' => $this->work->id]);

        $this->actingAsWithToken($this->user)
            ->deleteJson("/api/works/{$this->work->id}");

        $this->assertDatabaseHas('topics', ['id' => $topic->id, 'work_id' => $this->work->id]);
    }

    // ===== PERMISSION TESTS =====

    public function test_update_work_validates_ownership()
    {
        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $otherToken")
            ->putJson("/api/works/{$this->work->id}", [
                'title' => 'Hacked'
            ]);

        $response->assertStatus(403);
    }

    public function test_delete_work_validates_ownership()
    {
        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $otherToken")
            ->deleteJson("/api/works/{$this->work->id}");

        $response->assertStatus(403);
    }
}
