<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function actingAsWithToken($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer $token");
    }

    public function test_search_returns_results()
    {
        Work::factory()->create(['title' => 'One Piece']);
        Topic::factory()->create(['title' => 'My Review']);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=one');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'works' => [
                '*' => ['id', 'title', 'type']
            ],
            'topics',
            'total'
        ]);
    }

    public function test_search_requires_authentication()
    {
        $response = $this->getJson('/api/search?q=test');

        $response->assertStatus(401);
    }

    public function test_search_requires_minimum_query_length()
    {
        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=a');

        $response->assertStatus(422);
    }

    public function test_search_filters_by_type_works()
    {
        Work::factory()->create(['title' => 'Naruto']);
        Topic::factory()->create(['title' => 'Naruto Review']);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=naruto&type=works');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('works'));
        $this->assertEmpty($response->json('topics'));
    }

    public function test_search_filters_by_type_topics()
    {
        Work::factory()->create(['title' => 'Dragon Ball']);
        Topic::factory()->create(['title' => 'Dragon Ball Thoughts']);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=dragon&type=topics');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('works'));
        $this->assertCount(1, $response->json('topics'));
    }

    public function test_search_finds_in_description()
    {
        $work = Work::factory()->create([
            'title' => 'Amazing Book',
            'description' => 'Something interesting'
        ]);
        
        $this->assertDatabaseHas('works', ['title' => 'Amazing Book']);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=Amazing');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('works'));
    }

    public function test_search_finds_in_topic_content()
    {
        Topic::factory()->create([
            'title' => 'My Thoughts',
            'content' => 'This is an amazing manga'
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=manga');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('topics'));
    }

    public function test_search_returns_empty_for_no_matches()
    {
        Work::factory()->create(['title' => 'One Piece']);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=nonexistentterm123');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('total'));
    }

    public function test_search_is_case_insensitive()
    {
        Work::factory()->create(['title' => 'Attack on Titan']);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/search?q=ATTACK');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('works'));
    }
}
